<?php

/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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
use Nette\IOException;
use Nette\Utils\Finder;
use Nette\Utils\JsonException;
use stdClass;

/**
 * Scheduler configuration manager
 */
class SchedulerManager {

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private JsonFileManager $fileManager;

	/**
	 * @var SchedulerSchemaManager Scheduler JSON schema manager
	 */
	private SchedulerSchemaManager $schemaManager;

	/**
	 * @var TaskTimeManager Scheduler's task time specification manager
	 */
	private TaskTimeManager $timeManager;

	/**
	 * Constructor
	 * @param MainManager $mainManager Main configuration manager
	 * @param TaskTimeManager $timeManager Scheduler's task time specification manager
	 * @param CommandManager $commandManager Command manager
	 * @param SchedulerSchemaManager $schemaManager Scheduler JSON schema manager
	 */
	public function __construct(MainManager $mainManager, TaskTimeManager $timeManager, CommandManager $commandManager, SchedulerSchemaManager $schemaManager) {
		$this->timeManager = $timeManager;
		$cacheDir = $mainManager->getCacheDir();
		if (!is_readable($cacheDir) || !is_writable($cacheDir)) {
			$commandManager->run('chmod 777 ' . $cacheDir, true);
		}
		$path = $cacheDir . 'scheduler/';
		$this->fileManager = new JsonFileManager($path, $commandManager);
		$this->schemaManager = $schemaManager;
	}

	/**
	 * Deletes a task
	 * @param string $taskId Task ID
	 * @throws TaskNotFoundException
	 */
	public function delete(string $taskId): void {
		$this->fileManager->delete($this->getFileName($taskId));
	}

	/**
	 * Deletes all tasks
	 */
	public function deleteAll(): void {
		$dir = $this->fileManager->getDirectory();
		foreach (Finder::findFiles('*.json')->in($dir) as $file) {
			$this->fileManager->delete($file->getBasename('.json'));
		}
	}

	/**
	 * Returns task file name
	 * @param string $taskId Task ID
	 * @return string File name
	 * @throws TaskNotFoundException
	 */
	public function getFileName(string $taskId): string {
		$dir = $this->fileManager->getDirectory();
		foreach (Finder::findFiles('*.json')->in($dir) as $file) {
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
	 * @param string $taskId Task ID
	 * @return bool Is task exist?
	 */
	public function exist(string $taskId): bool {
		try {
			$this->getFileName($taskId);
			return true;
		} catch (TaskNotFoundException $e) {
			return false;
		}
	}

	/**
	 * Gets tasks
	 * @return array<mixed> Tasks
	 */
	public function list(): array {
		$tasks = [];
		$dir = $this->fileManager->getDirectory();
		foreach (Finder::findFiles('*.json')->in($dir) as $file) {
			$fileName = $file->getBasename('.json');
			try {
				$record = $this->readFile($fileName);
				if (is_object($record->task)) {
					$record->task = [$record->task];
				}
				$tasks[] = $record;
			} catch (InvalidJsonException | InvalidTaskMessageException | IOException | JsonException | TaskNotFoundException $e) {
				// Do nothing
			}
		}
		return $tasks;
	}

	/**
	 * Loads the task's configuration
	 * @param string $taskId Task ID
	 * @return stdClass Array for the form
	 * @throws InvalidJsonException
	 * @throws InvalidTaskMessageException
	 * @throws JsonException
	 * @throws NonexistentJsonSchemaException
	 * @throws TaskNotFoundException
	 */
	public function load(string $taskId): stdClass {
		$fileName = $this->getFileName($taskId);
		return $this->readFile($fileName);
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
		if (!isset($config->description)) {
			$config->description = '';
		}
		$this->timeManager->cronToArray($config);
		if (!isset($config->timeSpec->period)) {
			$config->timeSpec->period = 0;
		}
		$this->schemaManager->validate($config);
		$this->fileManager->write($fileName, $config);
	}

}
