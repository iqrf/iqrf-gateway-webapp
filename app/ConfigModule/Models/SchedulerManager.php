<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2019 IQRF Tech s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
declare(strict_types = 1);

namespace App\ConfigModule\Models;

use App\ConfigModule\Exceptions\InvalidTaskMessageException;
use App\ConfigModule\Exceptions\TaskNotFoundException;
use App\CoreModule\Exceptions\InvalidJsonException;
use App\CoreModule\Exceptions\NonexistentJsonSchemaException;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\JsonFileManager;
use App\ServiceModule\Exceptions\UnsupportedInitSystemException;
use App\ServiceModule\Models\ServiceManager;
use Nette\IOException;
use Nette\Utils\Finder;
use Nette\Utils\JsonException;
use SplFileInfo;
use stdClass;

/**
 * Scheduler configuration manager
 */
class SchedulerManager {

	/**
	 * @var GenericManager Generic config manager
	 */
	private $genericConfigManager;

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManager;

	/**
	 * @var SchedulerSchemaManager Scheduler JSON schema manager
	 */
	private $schemaManager;

	/**
	 * @var ServiceManager IQRF Gateway Daemon's service manager
	 */
	private $serviceManager;

	/**
	 * @var TaskTimeManager Scheduler's task time specification manager
	 */
	private $timeManager;

	/**
	 * Constructor
	 * @param MainManager $mainManager Main configuration manager
	 * @param GenericManager $genericManager Generic configuration manager
	 * @param TaskTimeManager $timeManager Scheduler's task time specification manager
	 * @param ServiceManager $serviceManager IQRF Gateway Daemon's service manager
	 * @param CommandManager $commandManager Command manager
	 * @param SchedulerSchemaManager $schemaManager Scheduler JSON schema manager
	 */
	public function __construct(MainManager $mainManager, GenericManager $genericManager, TaskTimeManager $timeManager, ServiceManager $serviceManager, CommandManager $commandManager, SchedulerSchemaManager $schemaManager) {
		$this->genericConfigManager = $genericManager;
		$this->serviceManager = $serviceManager;
		$this->timeManager = $timeManager;
		$cacheDir = $mainManager->getCacheDir();
		if (!is_readable($cacheDir) || !is_writable($cacheDir)) {
			$commandManager->run('chmod 777 ' . $cacheDir, true);
		}
		$path = $cacheDir . '/scheduler/';
		$this->fileManager = new JsonFileManager($path, $commandManager);
		$this->schemaManager = $schemaManager;
	}

	/**
	 * Deletes a task
	 * @param int $id Task ID
	 * @throws TaskNotFoundException
	 */
	public function delete(int $id): void {
		$this->fileManager->delete($this->getFileName($id));
		try {
			$this->serviceManager->restart();
		} catch (UnsupportedInitSystemException $e) {
			// Do nothing
		}
	}

	/**
	 * Returns task file name
	 * @param int $taskId Task ID
	 * @return string File name
	 * @throws TaskNotFoundException
	 */
	public function getFileName(int $taskId): string {
		$dir = $this->fileManager->getDirectory();
		foreach (Finder::findFiles('*.json')->in($dir) as $file) {
			assert($file instanceof SplFileInfo);
			$fileName = $file->getBasename('.json');
			try {
				$task = $this->fileManager->read($fileName);
				if ($task['taskId'] === $taskId) {
					return $fileName;
				}
			} catch (JsonException $e) {
				continue;
			}
		}
		throw new TaskNotFoundException();
	}

	/**
	 * Checks if the task exists
	 * @param int $taskId Task ID
	 * @return bool Is task exist?
	 */
	public function exist(int $taskId): bool {
		try {
			$this->getFileName($taskId);
			return true;
		} catch (TaskNotFoundException $e) {
			return false;
		}
	}

	/**
	 * Gets available messagings
	 * @return array<array<string>> Available messagings
	 */
	public function getMessagings(): array {
		$messagings = $this->genericConfigManager->getMessagings();
		unset($messagings['config.udp.title']);
		return $messagings;
	}

	/**
	 * Gets scheduler's services
	 * @return array<string> Scheduler's services
	 */
	public function getServices(): array {
		$this->genericConfigManager->setComponent('iqrf::SchedulerMessaging');
		return array_map(function (array $instance): string {
			return $instance['instance'];
		}, $this->genericConfigManager->list());
	}

	/**
	 * Gets tasks
	 * @return array<mixed> Tasks
	 */
	public function list(): array {
		$tasks = [];
		$dir = $this->fileManager->getDirectory();
		foreach (Finder::findFiles('*.json')->in($dir) as $file) {
			assert($file instanceof SplFileInfo);
			$fileName = $file->getBasename('.json');
			try {
				$data = $this->readFile($fileName);
				$task = [
					'id' => $data->taskId,
					'time' => $this->timeManager->getTime($data),
					'service' => $data->clientId,
					'messagings' => $this->getTaskMessagings($data->task),
					'mTypes' => $this->getTaskMessageTypes($data->task),
				];
				$tasks[] = $task;
			} catch (InvalidJsonException | InvalidTaskMessageException | IOException | JsonException | TaskNotFoundException $e) {
				// Do nothing
			}
		}
		usort($tasks, function (array $a, array $b): int {
			if ($a['id'] === $b['id']) {
				return 0;
			}
			return ($a['id'] < $b['id']) ? -1 : 1;
		});
		return $tasks;
	}

	/**
	 * Returns messagings used in tasks
	 * @param array<mixed> $tasks Tasks
	 * @return string Messagings used in tasks
	 */
	private function getTaskMessagings(array $tasks): string {
		$messagings = array_map(function (stdClass $task): string {
			return $task->messaging;
		}, $tasks);
		return implode(', ', $messagings);
	}

	/**
	 * Returns message types used in tasks
	 * @param array<mixed> $tasks Tasks
	 * @return string Message types used in tasks
	 */
	private function getTaskMessageTypes(array $tasks): string {
		$mTypes = array_map(function (stdClass $task): string {
			return $task->message->mType;
		}, $tasks);
		return implode(', ', $mTypes);
	}

	/**
	 * Loads the task's configuration
	 * @param int $id Task ID
	 * @return stdClass Array for the form
	 * @throws InvalidJsonException
	 * @throws InvalidTaskMessageException
	 * @throws JsonException
	 * @throws NonexistentJsonSchemaException
	 * @throws TaskNotFoundException
	 */
	public function load(int $id): stdClass {
		$fileName = $this->getFileName($id);
		return $this->readFile($fileName);
	}

	/**
	 * Fixes the task specification
	 * @param stdClass $config Scheduler task
	 */
	private function fixTasks(stdClass &$config): void {
		if (!is_array($config->task) && isset($config->task->message)) {
			$config->task = [$config->task];
		}
	}

	/**
	 * Reads a task
	 * @param string $fileName Task file name
	 * @return stdClass Task configuration
	 * @throws InvalidJsonException
	 * @throws InvalidTaskMessageException
	 * @throws JsonException
	 * @throws NonexistentJsonSchemaException
	 * @throws TaskNotFoundException
	 */
	private function readFile(string $fileName): stdClass {
		$config = $this->fileManager->read($fileName, false);
		$this->schemaManager->validate($config);
		$this->timeManager->cronToString($config);
		$this->fixTasks($config);
		return $config;
	}

	/**
	 * Saves the task's configuration
	 * @param stdClass $config Task's configuration
	 * @param string|null $fileName Task file name
	 * @throws InvalidTaskMessageException
	 * @throws JsonException
	 */
	public function save(stdClass $config, ?string $fileName): void {
		if ($fileName === null) {
			$fileName = strval($config->taskId);
		}
		foreach ($config->task as &$task) {
			if (!isset($task->message->data->timeout)) {
				unset($task->message->data->timeout);
			}
		}
		$this->timeManager->cronToArray($config);
		if (!isset($config->timeSpec->period)) {
			$config->timeSpec->period = 0;
		}
		$this->schemaManager->validate($config);
		$this->fileManager->write($fileName, $config);
	}

}
