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

namespace App\Model;

use App\Model\ConfigParser;
use Nette;
use Nette\Utils\ArrayHash;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;

class ConfigManager {

	use Nette\SmartObject;

	/**
	 * @var string
	 * @inject
	 */
	private $configDir;

	/**
	 * @var ConfigParser
	 * @inject
	 */
	private $configParser;

	/**
	 * Constructor
	 * @param string $configDir Directory with configuration files
	 * @param ConfigParser $configParser
	 */
	public function __construct($configDir, ConfigParser $configParser) {
		$this->configDir = $configDir;
		$this->configParser = $configParser;
	}

	/**
	 * Read JSON file and decode JSON to array
	 * @param string $name File name (without .json)
	 * @return array
	 */
	public function read($name) {
		$json = FileSystem::read($this->configDir . '/' . $name . '.json');
		return Json::decode($json, Json::FORCE_ARRAY);
	}

	/**
	 * Encode JSON from array and write JSON file
	 * @param string $name File name (without .json)
	 * @param array $array JSON array
	 */
	public function write($name, $array) {
		$fileName = $this->configDir . '/' . $name . '.json';
		FileSystem::write($fileName, Json::encode($array, Json::PRETTY), NULL);
	}

	/**
	 * Delete Base service setting
	 * @param int $id Base service ID
	 */
	public function deleteBaseService($id) {
		$fileName = 'BaseService';
		$json = $this->read($fileName);
		unset($json['Instances'][$id]);
		$json['Instances'] = array_values($json['Instances']);
		$this->write($fileName, $json);
	}

	/**
	 * Save Base service setting
	 * @param ArrayHash $array Base service settings
	 * @param int $id Base service ID
	 */
	public function saveBaseService(ArrayHash $array, $id = 0) {
		$fileName = 'BaseService';
		$json = $this->configParser->baseServiceToJson($this->read($fileName), $array, $id);
		$this->write($fileName, $json);
	}

	/**
	 * Save components setting
	 * @param array $components Components settings
	 */
	public function saveComponents($components) {
		$fileName = 'config';
		$json = $this->read($fileName);
		$json['Components'] = $this->configParser->componentsToJson($components);
		$this->write($fileName, $json);
	}

	/**
	 * Save Instances setting
	 * @param string $fileName File name (without .json)
	 * @param ArrayHash $array Instance settings
	 * @param int $id Instance ID
	 */
	public function saveInstances($fileName, ArrayHash $array, $id = 0) {
		$json = $this->read($fileName);
		$json['Instances'] = $this->configParser->instancesToJson($json['Instances'], $array, $id);
		$this->write($fileName, $json);
	}

	/**
	 * Save Main daemon configuration
	 * @param ArrayHash $array Main settings
	 */
	public function saveMain(ArrayHash $array) {
		$fileName = 'config';
		$json = $this->read($fileName);
		$this->write($fileName, array_merge($json, (array) $array));
	}

	/**
	 * Save scheduler setting
	 * @param ArrayHash $array Scheduler settings
	 * @param int $id Scheduler ID
	 */
	public function saveScheduler(ArrayHash $array, $id = 0) {
		$fileName = 'Scheduler';
		$json = $this->configParser->schedulerToJson($this->read($fileName), $array, $id);
		$this->write($fileName, $json);
	}

}
