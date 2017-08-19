<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace App\ConfigModule\Model;

use App\Model\JsonFileManager;
use Nette;
use Nette\Utils\ArrayHash;

class SchedulerManager {

	use Nette\SmartObject;

	/**
	 * @var JsonFileManager
	 */
	private $fileManager;

	/**
	 * @var string
	 */
	private $fileName = 'Scheduler';

	/**
	 * Constructor
	 * @param JsonFileManager $fileManager
	 */
	public function __construct(JsonFileManager $fileManager) {
		$this->fileManager = $fileManager;
	}

	/**
	 * Delete task
	 * @param int $id Task ID
	 */
	public function delete($id) {
		$json = $this->fileManager->read($this->fileName);
		unset($json['TasksJson'][$id]);
		$json['TasksJson'] = array_values($json['TasksJson']);
		$this->fileManager->write($this->fileName, $json);
	}

	public function getTasks() {
		$json = $this->fileManager->read($this->fileName);
		return $json['TasksJson'];
	}

	/**
	 * Convert Task JSON array to Task configuration form array
	 * @param int $id Task ID
	 * @return array Array for form
	 */
	public function load($id = 0) {
		$json = $this->fileManager->read($this->fileName);
		$tasks = $json['TasksJson'];
		if ($id > count($tasks)) {
			return [];
		}
		$data = $tasks[$id];
		$scheduler = [];
		$scheduler['time'] = $data['time'];
		$scheduler['service'] = $data['service'];
		if (array_key_exists('sensors', $data['message'])) {
			$sensors = implode(PHP_EOL, $data['message']['sensors']);
			unset($scheduler['message']['sensors']);
			$scheduler += $data['message'];
			$scheduler['sensors'] = $sensors;
		} else {
			$scheduler += $data['message'];
		}
		return $scheduler;
	}

	/**
	 * Save scheduler setting
	 * @param ArrayHash $array Scheduler settings
	 * @param int $id Task ID
	 */
	public function save(ArrayHash $array, $id = 0) {
		$json = $this->saveJson($this->fileManager->read($this->fileName), $array, $id);
		$this->fileManager->write($this->fileName, $json);
	}

	/**
	 * Convert Task configuration form array to JSON array
	 * @param array $scheduler Original Task JSON array
	 * @param ArrayHash $update Changed settings
	 * @param int $id Task ID
	 * @return array JSON array
	 */
	public function saveJson(array $scheduler, ArrayHash $update, $id) {
		$data = [];
		$data['time'] = $update['time'];
		$data['service'] = $update['service'];
		unset($update['service'], $update['time']);
		if (array_key_exists('sensors', $update)) {
			$sensors = explode(PHP_EOL, $update['sensors']);
			unset($update['sensors']);
			$update['sensors'] = $sensors;
		}
		$data['message'] = (array) $update;
		$scheduler['TasksJson'][$id] = $data;
		return $scheduler;
	}

}
