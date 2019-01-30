<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2018 IQRF Tech s.r.o.
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
	 * Add task
	 * @param string $type Task type
	 * @throws JsonException
	 */
	public function add(string $type): void {
		$taskManager = new JsonFileManager(__DIR__ . '/../json');
		$tasks = $taskManager->read('Scheduler');
		if (array_key_exists($type, $tasks)) {
			$task = $tasks[$type];
			$task['taskId'] = (new DateTime())->getTimestamp();
			$this->save($task);
		}
	}

	/**
	 * Delete task
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
	 * Get last ID
	 * @return int Last ID in array
	 * @throws JsonException
	 */
	public function getLastId(): int {
		$tasks = $this->getTaskFiles();
		end($tasks);
		return intval(key($tasks));
	}

	/**
	 * Get task's files
	 * @return string[] Files with tasks
	 * @throws JsonException
	 */
	public function getTaskFiles(): array {
		$dir = $this->fileManager->getDirectory();
		$files = [];
		foreach (Finder::findFiles('*.json')->from($dir) as $file) {
			$fileName = Strings::replace($file->getRealPath(), ['~^' . realpath($dir) . '/~', '/.json$/'], '');
			$json = $this->fileManager->read($fileName);
			if (array_key_exists('taskId', $json)) {
				$files[$json['taskId']] = $fileName;
			}
		}
		asort($files);
		return $files;
	}

	/**
	 * Get task's time
	 * @param mixed[] $task Task
	 * @return string Task's time
	 */
	private function getTime(array $task): string {
		$timeSpec = $task['timeSpec'];
		if ($timeSpec['cronTime'] !== '') {
			return $timeSpec['cronTime'];
		}
		if ($timeSpec['exactTime']) {
			return $timeSpec['startTime'];
		}
		if ($timeSpec['periodic']) {
			$period = $timeSpec['period'];
			if ($period < 60) {
				return 'every ' . $period . ' seconds';
			} elseif ($period < 3600) {
				return 'every ' . gmdate('m:s', $period) . ' minutes';
			} else {
				return 'every ' . gmdate('H:m:s', $period) . ' hours';
			}
		}
		return '';
	}

	/**
	 * Get available messagings
	 * @return string[][] Available messagings
	 * @throws JsonException
	 */
	public function getMessagings(): array {
		$messagings = $this->genericConfigManager->getMessagings();
		unset($messagings['config.udp.title']);
		return $messagings;
	}

	/**
	 * Get scheduler's services
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
	 * Get tasks in Scheduler
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
	 * Get DPA request from JSON
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
	 * Fix HWPID format
	 * @param int|null $hwpId HWPID to fix
	 * @return string Fixed HWPID
	 */
	public function fixHwpid(?int $hwpId = null): string {
		$data = Strings::padLeft(dechex($hwpId & 255), 2, '0') . '.';
		$data .= Strings::padLeft(dechex($hwpId >> 8), 2, '0');
		return $data;
	}

	/**
	 * Convert Task JSON array to Task configuration form array
	 * @param int $id Task ID
	 * @return mixed[] Array for form
	 * @throws JsonException
	 */
	public function load(int $id): array {
		$files = $this->getTaskFiles();
		if (!isset($files[$id])) {
			return [];
		}
		$this->fileName = strval($files[$id]);
		return $this->fileManager->read($this->fileName);
	}

	/**
	 * Save task's setting
	 * @param mixed[] $array Task's settings
	 * @throws JsonException
	 */
	public function save(array $array): void {
		if (!isset($this->fileName)) {
			$this->fileName = strval($array['taskId']);
		}
		$this->fileManager->write($this->fileName, $array);
	}

}
