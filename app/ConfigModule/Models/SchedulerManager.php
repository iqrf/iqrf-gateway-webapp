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

use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\JsonFileManager;
use App\ServiceModule\Exceptions\NotSupportedInitSystemException;
use App\ServiceModule\Models\ServiceManager;
use Nette\IOException;
use Nette\SmartObject;
use Nette\Utils\Finder;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;
use SplFileInfo;
use stdClass;

/**
 * Scheduler configuration manager
 */
class SchedulerManager {

	use SmartObject;

	/**
	 * @var GenericManager Generic config manager
	 */
	private $genericConfigManager;

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManager;

	/**
	 * @var string File name (without .json)
	 */
	private $fileName;

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
	 */
	public function __construct(MainManager $mainManager, GenericManager $genericManager, TaskTimeManager $timeManager, ServiceManager $serviceManager, CommandManager $commandManager) {
		$this->genericConfigManager = $genericManager;
		$this->serviceManager = $serviceManager;
		$this->timeManager = $timeManager;
		try {
			$path = $mainManager->load()['cacheDir'] . '/scheduler/';
		} catch (IOException | JsonException $e) {
			$path = '/var/cache/iqrf-gateway-daemon/scheduler/';
		}
		$this->fileManager = new JsonFileManager($path, $commandManager);
	}

	/**
	 * Deletes a task
	 * @param int $id Task ID
	 * @throws JsonException
	 */
	public function delete(int $id): void {
		$files = $this->getTaskFiles();
		if (isset($files[$id])) {
			$this->fileManager->delete($files[$id]);
			try {
				$this->serviceManager->restart();
			} catch (NotSupportedInitSystemException $e) {
				// Do nothing
			}
		}
	}

	/**
	 * Gets task's files
	 * @return string[] Files with tasks
	 * @throws JsonException
	 */
	public function getTaskFiles(): array {
		$dir = $this->fileManager->getDirectory();
		$files = [];
		/**
		 * @var SplFileInfo $file File info
		 */
		foreach (Finder::findFiles('*.json')->from($dir) as $file) {
			$dirPathPattern = ['~^' . realpath($dir) . '/~', '/.json$/'];
			$fileName = Strings::replace($file->getRealPath(), $dirPathPattern, '');
			$json = $this->fileManager->read($fileName);
			if (array_key_exists('taskId', $json)) {
				$files[$json['taskId']] = $fileName;
			}
		}
		asort($files);
		return $files;
	}

	/**
	 * Gets available messagings
	 * @return string[][] Available messagings
	 * @throws JsonException
	 */
	public function getMessagings(): array {
		$messagings = $this->genericConfigManager->getMessagings();
		unset($messagings['config.udp.title']);
		return $messagings;
	}

	/**
	 * Gets scheduler's services
	 * @return string[] Scheduler's services
	 * @throws JsonException
	 */
	public function getServices(): array {
		$services = [];
		$this->genericConfigManager->setComponent('iqrf::SchedulerMessaging');
		foreach ($this->genericConfigManager->list() as $instance) {
			$services[] = $instance['instance'];
		}
		return $services;
	}

	/**
	 * Gets tasks
	 * @return mixed[] Tasks
	 * @throws JsonException
	 */
	public function list(): array {
		$tasks = [];
		foreach ($this->getTaskFiles() as $id => $fileName) {
			$data = $this->load($id);
			$task = [
				'id' => $data->taskId,
				'time' => $this->timeManager->getTime($data),
				'service' => $data->clientId,
				'messaging' => $this->getTaskMessagings($data->task),
				'mType' => $this->getTaskMessageTypes($data->task),
			];
			$tasks[] = $task;
		}
		return $tasks;
	}

	/**
	 * Returns messagings used in tasks
	 * @param mixed[] $tasks Tasks
	 * @return string Messagings used in tasks
	 */
	private function getTaskMessagings(array $tasks): string {
		$messagings = [];
		foreach ($tasks as $task) {
			$messagings[] = $task->messaging;
		}
		return implode(', ', $messagings);
	}

	/**
	 * Returns message types used in tasks
	 * @param mixed[] $tasks Tasks
	 * @return string Message types used in tasks
	 */
	private function getTaskMessageTypes(array $tasks): string {
		$mTypes  = [];
		foreach ($tasks as $task) {
			$mTypes[] = $task->message->mType;
		}
		return implode(', ', $mTypes);
	}

	/**
	 * Loads the task's configuration
	 * @param int $id Task ID
	 * @return stdClass Array for the form
	 * @throws JsonException
	 */
	public function load(int $id): stdClass {
		$files = $this->getTaskFiles();
		if (!isset($files[$id])) {
			return new stdClass();
		}
		$this->fileName = strval($files[$id]);
		$config = $this->fileManager->read($this->fileName, false);
		$this->timeManager->cronToString($config);
		$this->fixTasks($config);
		return $config;
	}

	/**
	 * Fixes the HWPID format
	 * @param int|null $hwpId HWPID to fix
	 * @return string Fixed HWPID
	 */
	public function fixHwpid(?int $hwpId = null): string {
		$data = Strings::padLeft(dechex($hwpId & 255), 2, '0') . '.';
		return $data . Strings::padLeft(dechex($hwpId >> 8), 2, '0');
	}

	/**
	 * Fixes the task specification
	 * @param stdClass $config Scheduler task
	 */
	public function fixTasks(stdClass &$config): void {
		if (!is_array($config->task) && isset($config->task->message)) {
			$config->task = [$config->task];
		}
	}

	/**
	 * Saves the task's configuration
	 * @param stdClass $config Task's configuration
	 * @throws JsonException
	 */
	public function save(stdClass $config): void {
		if (!isset($this->fileName)) {
			$this->fileName = strval($config->taskId);
		}
		foreach ($config->task as &$task) {
			if (!isset($task->message->data->timeout)) {
				unset($task->message->data->timeout);
			}
		}
		$this->timeManager->cronToArray($config);
		$this->fileManager->write($this->fileName, $config);
	}

}
