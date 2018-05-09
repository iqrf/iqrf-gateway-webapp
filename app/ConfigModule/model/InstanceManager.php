<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2018 IQRF Tech s.r.o.
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
declare(strict_types=1);

namespace App\ConfigModule\Model;

use App\ConfigModule\Model\BaseServiceManager;
use App\Model\JsonFileManager;
use Nette;
use Nette\Utils\ArrayHash;

class InstanceManager {

	use Nette\SmartObject;

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManager;

	/**
	 * @var string File name (without .json)
	 */
	private $fileName;

	/**
	 * @var BaseServiceManager Base service manager
	 */
	private $baseServiceManager;

	/**
	 * Constructor
	 * @param JsonFileManager $fileManager JSON file manager
	 */
	public function __construct(JsonFileManager $fileManager, BaseServiceManager $baseServiceManager) {
		$this->fileManager = $fileManager;
		$this->baseServiceManager = $baseServiceManager;
	}

	/**
	 * Add new Instance
	 * @param ArrayHash $array Instance's settings
	 */
	public function add(ArrayHash $array) {
		$this->deleteByName($array['Name']);
		$id = count($this->getInstances());
		$this->save($array, $id);
	}

	/**
	 * Delete Instance setting
	 * @param int $id Instance ID
	 */
	public function delete(int $id) {
		$json = $this->fileManager->read($this->fileName);
		$instanceName = $json['Instances'][$id]['Name'];
		$this->baseServiceManager->deleteByInstanceName($instanceName);
		unset($json['Instances'][$id]);
		$json['Instances'] = array_values($json['Instances']);
		$this->fileManager->write($this->fileName, $json);
	}

	/**
	 * Delete Instance setting
	 * @param string $name Instance name
	 */
	public function deleteByName(string $name) {
		$json = $this->fileManager->read($this->fileName);
		foreach ($json['Instances'] as $key => $instance) {
			if ($instance['Name'] === $name) {
				$instanceName = $json['Instances'][$key]['Name'];
				$this->baseServiceManager->deleteByInstanceName($instanceName);
				unset($json['Instances'][$key]);
			}
		}
		$json['Instances'] = array_values($json['Instances']);
		$this->fileManager->write($this->fileName, $json);
	}

	/**
	 * Get list of Instances
	 * @return array Instances
	 */
	public function getInstances(): array {
		$json = $this->fileManager->read($this->fileName);
		return $json['Instances'];
	}

	/**
	 * Get list of names of Instances
	 * @return array Names of instances
	 */
	public function getInstancesNames(): array {
		$instances = $this->getInstances();
		$data = [];
		foreach ($instances as $instance) {
			$data[$instance['Name']] = $instance['Name'];
		}
		return $data;
	}

	/**
	 * Convert Instances configuration form array to JSON array
	 * @param int $id Interface ID
	 * @return array Array for form
	 */
	public function load(int $id = 0): array {
		$json = $this->fileManager->read($this->fileName);
		$instances = $json['Instances'];
		if ($id >= count($instances)) {
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
	public function save(ArrayHash $array, int $id = 0) {
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
	public function saveJson(array $instances, ArrayHash $update, int $id): array {
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
	public function setFileName(string $fileName) {
		$this->fileName = $fileName;
	}

}
