<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017 IQRF Tech s.r.o.
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

class InstanceManager {

	use Nette\SmartObject;

	/**
	 * @var JsonFileManager
	 */
	private $fileManager;

	/**
	 *
	 * @var string File name (without .json)
	 */
	private $fileName;

	/**
	 * Constructor
	 * @param JsonFileManager $fileManager
	 */
	public function __construct(JsonFileManager $fileManager) {
		$this->fileManager = $fileManager;
	}

	/**
	 * Add new Instance
	 * @param ArrayHash $array Instance's settings
	 */
	public function add(ArrayHash $array) {
		$id = count($this->getInstances());
		$this->save($array, $id);
	}

	/**
	 * Delete Instance setting
	 * @param int $id Instance ID
	 */
	public function delete($id) {
		$json = $this->fileManager->read($this->fileName);
		unset($json['Instances'][$id]);
		$json['Instances'] = array_values($json['Instances']);
		$this->fileManager->write($this->fileName, $json);
	}

	/**
	 * Get list of Instances
	 * @return array Instances
	 */
	public function getInstances() {
		$json = $this->fileManager->read($this->fileName);
		return $json['Instances'];
	}

	/**
	 * Convert Instances configuration form array to JSON array
	 * @param int $id Interface ID
	 * @return array Array for form
	 */
	public function load($id = 0) {
		$json = $this->fileManager->read($this->fileName);
		$instances = $json['Instances'];
		if ($id > count($instances)) {
			return [];
		}
		$data = $instances[$id];
		$instance = $data['Properties'];
		$instance['Name'] = $data['Name'];
		$instance['Enabled'] = $data['Enabled'];
		return $instance;
	}

	/**
	 * Save Instances setting
	 * @param ArrayHash $array Instance settings
	 * @param int $id Instance ID
	 */
	public function save(ArrayHash $array, $id = 0) {
		$json = $this->fileManager->read($this->fileName);
		$json['Instances'] = $this->saveJson($json['Instances'], $array, $id);
		$this->fileManager->write($this->fileName, $json);
	}

	/**
	 * Convert array from Interfaces configuration form to JSON
	 * @param array $instances Original Instances JSON array
	 * @param ArrayHash $update Changed settings
	 * @param int $id Interface ID
	 * @return array JSON array
	 */
	public function saveJson(array $instances, ArrayHash $update, $id) {
		$instance = [];
		$instance['Name'] = $update['Name'];
		$instance['Enabled'] = $update['Enabled'];
		unset($update['Name'], $update['Enabled']);
		$instance['Properties'] = (array) $update;
		$instances[$id] = $instance;
		return $instances;
	}

	/**
	 * Set file name
	 * @param string $fileName File name (without .json)
	 */
	public function setFileName($fileName) {
		$this->fileName = $fileName;
	}

}
