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

class BaseServiceManager {

	use Nette\SmartObject;

	/**
	 * @var JsonFileManager
	 */
	private $fileManager;

	/**
	 * @var string File name (without .json)
	 */
	private $fileName = 'BaseService';

	/**
	 * Constructor
	 * @param JsonFileManager $fileManager
	 */
	public function __construct(JsonFileManager $fileManager) {
		$this->fileManager = $fileManager;
	}

	/**
	 * Delete Base service setting
	 * @param int $id Base service ID
	 */
	public function delete($id) {
		$json = $this->fileManager->read($this->fileName);
		unset($json['Instances'][$id]);
		$json['Instances'] = array_values($json['Instances']);
		$this->fileManager->write($this->fileName, $json);
	}

	/**
	 * Get list of Base services
	 * @return array Base services
	 */
	public function getServices() {
		$json = $this->fileManager->read($this->fileName);
		return $json['Instances'];
	}

	/**
	 * Load current settings into Base Service configuration form
	 * @param int $id Base Service ID
	 * @return array Array for form
	 */
	public function load($id = 0) {
		$json = $this->fileManager->read($this->fileName);
		$instances = $json['Instances'];
		if ($id > count($instances)) {
			return [];
		}
		$data = $instances[$id];
		$service = [];
		$service['Name'] = $data['Name'];
		$service['Messaging'] = $data['Messaging'];
		$service['Serializers'] = (array) $data['Serializers'];
		if (!empty($data['Properties'])) {
			$service['Properties'] = (array) $data['Properties'];
		} else {
			$service['Properties']['AsyncDpaMessage'] = false;
		}
		return $service;
	}

	/**
	 * Save Base service setting
	 * @param ArrayHash $array Base service settings
	 * @param int $id Base service ID
	 */
	public function save(ArrayHash $array, $id = 0) {
		$json = $this->saveJson($this->fileManager->read($this->fileName), $array, $id);
		$this->fileManager->write($this->fileName, $json);
	}

	/**
	 * Convert array from Base Service configuration form to JSON
	 * @param array $services Base Service JSON array
	 * @param ArrayHash $update Changed settings
	 * @param int $id Base Service ID
	 * @return array JSON array
	 */
	public function saveJson(array $services, ArrayHash $update, $id) {
		$service = [];
		$service['Name'] = $update['Name'];
		$service['Messaging'] = $update['Messaging'];
		$service['Serializers'] = array_values((array) $update['Serializers']);
		if (isset($update['Properties'])) {
			$service['Properties'] = (array) $update['Properties'];
		} else {
			$service['Properties'] = [];
		}
		$services['Instances'][$id] = $service;
		return $services;
	}

}
