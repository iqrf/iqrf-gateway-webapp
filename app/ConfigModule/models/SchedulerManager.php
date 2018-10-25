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
use Nette\IOException;
use Nette\SmartObject;
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
	private $fileName = 'Tasks';

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
			$path = '/var/cache/iqrfgd2/scheduler/';
		}
		$this->fileManager = new JsonFileManager($path);
	}

	/**
	 * Add task
	 * @param string $type Task type
	 * @throws JsonException
	 */
	public function add(string $type): void {
		$json = $this->fileManager->read($this->fileName);
		$taskManager = new JsonFileManager(__DIR__ . '/../json');
		$tasks = $taskManager->read('Scheduler');
		$task = array_key_exists($type, $tasks) ? $tasks[$type] : null;
		array_push($json['TasksJson'], $task);
		$this->fileManager->write($this->fileName, $json);
	}

	/**
	 * Delete task
	 * @param int $id Task ID
	 * @throws JsonException
	 */
	public function delete(int $id): void {
		$json = $this->fileManager->read($this->fileName);
		unset($json['TasksJson'][$id]);
		$json['TasksJson'] = array_values($json['TasksJson']);
		$this->fileManager->write($this->fileName, $json);
	}

	/**
	 * Get last ID
	 * @return int Last ID in array
	 * @throws JsonException
	 */
	public function getLastId(): int {
		$json = $this->fileManager->read($this->fileName);
		$tasks = $json['TasksJson'];
		end($tasks);
		return intval(key($tasks));
	}

	/**
	 * Get available messagings
	 * @return array Available messagings
	 * @throws JsonException
	 */
	public function getMessagings(): array {
		$messagings = $this->genericConfigManager->getMessagings();
		unset($messagings['config.udp.title']);
		return $messagings;
	}

	/**
	 * Get scheduler's services
	 * @return array Scheduler's services
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
	 * @return array Tasks
	 * @throws JsonException
	 */
	public function list(): array {
		$jsonTasks = $this->fileManager->read($this->fileName)['TasksJson'];
		$tasks = [];
		foreach ($jsonTasks as $id => $jsonTask) {
			$message = $jsonTask['task']['message'];
			$task = [
				'time' => $jsonTask['time'],
				'service' => $jsonTask['service'],
				'messaging' => $jsonTask['task']['messaging'],
				'mType' => $message['mType'] ?? '',
				'request' => $this->getRequest($message),
				'id' => $id,
			];
			array_push($tasks, $task);
		}
		return $tasks;
	}

	/**
	 * Get DPA request from JSON
	 * @param array $data JSON
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
					$packet .= '.' . implode('.',$request['pData']);
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
	 * @return array Array for form
	 * @throws JsonException
	 */
	public function load(int $id): array {
		$json = $this->fileManager->read($this->fileName);
		$tasks = $json['TasksJson'];
		if ($id > count($tasks)) {
			return [];
		}
		return $tasks[$id];
	}

	/**
	 * Save scheduler setting
	 * @param array $array Scheduler settings
	 * @param int $id Task ID
	 * @throws JsonException
	 */
	public function save(array $array, int $id): void {
		$json = $this->fileManager->read($this->fileName);
		$json['TasksJson'][$id] = $array;
		$this->fileManager->write($this->fileName, $json);
	}

}
