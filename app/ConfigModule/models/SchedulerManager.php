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

use App\CoreModule\Models\JsonFileManager;
use DateTime;
use Nette\IOException;
use Nette\SmartObject;
use Nette\Utils\Finder;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;
use SplFileInfo;
use Throwable;

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
	 * @var MainManager Main configuration manager
	 */
	private $mainConfigManager;

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManager;

	/**
	 * @var string File name (without .json)
	 */
	private $fileName;

	/**
	 * Constructor
	 * @param MainManager $mainManager Main configuration manager
	 * @param GenericManager $genericManager Generic configuration manager
	 */
	public function __construct(MainManager $mainManager, GenericManager $genericManager) {
		$this->genericConfigManager = $genericManager;
		$this->mainConfigManager = $mainManager;
		try {
			$path = $this->mainConfigManager->load()['cacheDir'] . '/scheduler/';
		} catch (IOException | JsonException $e) {
			$path = '/var/cache/iqrf-gateway-daemon/scheduler/';
		}
		$this->fileManager = new JsonFileManager($path);
	}

	/**
	 * Adds a new task
	 * @param string $type Task type
	 * @throws JsonException
	 */
	public function add(string $type): void {
		$task = $this->loadType($type);
		if ($task !== null) {
			$this->save($task);
		}
	}

	/**
	 * Converts a cron time from a string to an array
	 * @param mixed[] $config Tasks's configuration
	 */
	private function cronToArray(array &$config): void {
		$cron = &$config['timeSpec']['cronTime'];
		$cron = explode(' ', $cron);
		$cron = array_slice($cron, 0, 7);
	}

	/**
	 * Converts a cron time from an array to a string
	 * @param mixed[] $config Task's configuration
	 */
	private function cronToString(array &$config): void {
		$cron = &$config['timeSpec']['cronTime'];
		$cron = implode(' ', $cron);
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
	 * Gets task's time
	 * @param mixed[] $task Task
	 * @return string Task's time
	 */
	private function getTime(array $task): string {
		$timeSpec = $task['timeSpec'];
		if ($timeSpec['cronTime'] !== '') {
			return $timeSpec['cronTime'];
		}
		if ($timeSpec['exactTime']) {
			return 'one shot (' . $timeSpec['startTime'] . ')';
		}
		if ($timeSpec['periodic']) {
			$period = $timeSpec['period'];
			if ($period < 60) {
				$format = 'every %s seconds';
			} elseif ($period < 3600) {
				$format = 'every %i:%S minutes';
			} else {
				$format = 'every %h:%I:%S hours';
			}
			return $this->formatPeriod($period, $format) ?? '';
		}
		return '';
	}

	/**
	 * Formats a period in seconds
	 * @param int $seconds Perion in seconds
	 * @param string $format Period's format
	 * @return string|null Formatted period
	 */
	private function formatPeriod(int $seconds, string $format): ?string {
		try {
			$date0 = new DateTime('@0');
			$date1 = new DateTime('@0' . $seconds);
			return $date1->diff($date0)->format($format);
		} catch (Throwable $e) {
			return null;
		}
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
			$message = $data['task']['message'];
			$task = [
				'id' => $data['taskId'],
				'time' => $this->getTime($data),
				'service' => $data['clientId'],
				'messaging' => $data['task']['messaging'],
				'mType' => $message['mType'] ?? '',
				'request' => $this->getRequest($message),
			];
			array_push($tasks, $task);
		}
		return $tasks;
	}

	/**
	 * Gets DPA request from JSON
	 * @param mixed[] $data JSON
	 * @return string DPA request
	 */
	public function getRequest(array $data): string {
		if ((!isset($data['mType'])) || (!isset($data['data']['req']))) {
			return '';
		}
		$request = $data['data']['req'];
		switch ($data['mType']) {
			case 'iqrfRaw':
				return $request['rData'];
			case 'iqrfRawHdp':
				$packet = Strings::padLeft(dechex($request['nAdr']), 2, '0') . '.00.';
				$packet .= Strings::padLeft(dechex($request['pNum']), 2, '0') . '.';
				$packet .= Strings::padLeft(dechex($request['pCmd']), 2, '0') . '.';
				$packet .= $this->fixHwpid($request['hwpId'] ?? 65535);
				if (isset($request['pData']) && $request['pData'] !== []) {
					foreach ($request['pData'] as &$byte) {
						$byte = Strings::padLeft(dechex($byte), 2, '0');
					}
					$packet .= '.' . implode('.', $request['pData']);
				}
				return $packet;
			default:
				return '';
		}
	}

	/**
	 * Fixes the HWPID format
	 * @param int|null $hwpId HWPID to fix
	 * @return string Fixed HWPID
	 */
	public function fixHwpid(?int $hwpId = null): string {
		$data = Strings::padLeft(dechex($hwpId & 255), 2, '0') . '.';
		$data .= Strings::padLeft(dechex($hwpId >> 8), 2, '0');
		return $data;
	}

	/**
	 * Loads the task's configuration
	 * @param int $id Task ID
	 * @return mixed[] Array for the form
	 * @throws JsonException
	 */
	public function load(int $id): array {
		$files = $this->getTaskFiles();
		if (!isset($files[$id])) {
			return [];
		}
		$this->fileName = strval($files[$id]);
		$config = $this->fileManager->read($this->fileName);
		$this->cronToString($config);
		return $config;
	}

	/**
	 * Loads the task's configuration from the task's message type
	 * @param string $type Task's message type
	 * @return mixed[]|null Array for the form
	 * @throws JsonException
	 */
	public function loadType(string $type): ?array {
		$taskManager = new JsonFileManager(__DIR__ . '/../json');
		$tasks = $taskManager->read('Scheduler');
		if (array_key_exists($type, $tasks)) {
			$task = $tasks[$type];
			try {
				$task['taskId'] = (new DateTime())->getTimestamp();
			} catch (Throwable $e) {
				$task['taskId'] = null;
			}
			return $task;
		}
		return null;
	}

	/**
	 * Saves the task's configuration
	 * @param mixed[] $config Task's configuration
	 * @throws JsonException
	 */
	public function save(array $config): void {
		if (!isset($this->fileName)) {
			$this->fileName = strval($config['taskId']);
		}
		if (!isset($config['task']['message']['data']['timeout'])) {
			unset($config['task']['message']['data']['timeout']);
		}
		$this->cronToArray($config);
		$this->fileManager->write($this->fileName, $config);
	}

}
