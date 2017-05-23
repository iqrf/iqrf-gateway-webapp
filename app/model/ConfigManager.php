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
	 * Save components setting
	 * @param array $components Components settings
	 */
	public function saveComponents($components) {
		$json = $this->read('config');
		$json['Components'] = $this->configParser->componentsToJson($components);
		$this->write('config', $json);
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

}
