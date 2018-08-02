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

namespace App\ConfigModule\Model;

use App\Model\JsonFileManager;
use App\Model\JsonSchemaManager;
use Nette;
use Nette\Utils\Arrays;
use Nette\Utils\ArrayHash;
use Nette\Utils\Finder;
use Nette\Utils\Strings;

class GenericManager {

	use Nette\SmartObject;

	/**
	 * @var string Component type
	 */
	private $component;

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManager;

	/**
	 * @var string File name (without .json)
	 */
	private $fileName;

	/**
	 * @var JsonSchemaManager JSON schema manager
	 */
	private $schemaManager;

	/**
	 * Constructor
	 * @param JsonFileManager $fileManager JSON file manager
	 * @param JsonSchemaManager $schemaManager JSON schema manager
	 */
	public function __construct(JsonFileManager $fileManager, JsonSchemaManager $schemaManager) {
		$this->fileManager = $fileManager;
		$this->schemaManager = $schemaManager;
	}

	/**
	 * Delete a configuration
	 */
	public function delete() {
		$this->fileManager->delete($this->fileName);
	}

	/**
	 * Load a configuration
	 * @return array Array for form
	 */
	public function load(): array {
		$configuration = $this->fileManager->read($this->fileName);
		return $this->fixRequiredInterfaces($configuration);
	}

	/**
	 * Save configuration
	 * @param ArrayHash $array Settings
	 */
	public function save(ArrayHash $array) {
		$component = ['component' => $this->component];
		$settings = Arrays::mergeTree($component, (array) $array);
		$this->schemaManager->validate(ArrayHash::from($settings));
		$this->fileManager->write($this->fileName, ArrayHash::from($settings));
	}

	/**
	 * Set component type
	 * @param string $component Component name
	 */
	public function setComponent(string $component) {
		$this->component = $component;
		$this->schemaManager->setSchemaFromComponent($component);
	}

	/**
	 * Set file name
	 * @param string $fileName File name (without .json)
	 */
	public function setFileName(string $fileName) {
		$this->fileName = $fileName;
	}

	/**
	 * Get component's instance
	 * @return array Component's instances
	 */
	public function getInstances(): array {
		$files = $this->getInstanceFiles();
		$instances = [];
		foreach ($files as $file) {
			$this->fileName = $file;
			$instances[] = $this->load();
		}
		return $instances;
	}

	/**
	 * Get component's instance files
	 * @return array Files with component's instances
	 */
	public function getInstanceFiles(): array {
		$dir = $this->fileManager->getDirectory();
		$instances = [];
		foreach (Finder::findFiles('*.json')->exclude('config.json')->from($dir) as $file) {
			$fileName = Strings::replace($file->getRealPath(), ['~^' . realpath($dir) . '/~', '/.json$/'], '');
			$json = $this->fileManager->read($fileName);
			if (array_key_exists('component', $json) && $json['component'] === $this->component) {
				$instances[] = $fileName;
			}
		}
		return $instances;
	}

	/**
	 * Get available messagings
	 * @return array Available messagings
	 */
	public function getMessagings(): array {
		$components = [
			'mq' => 'iqrf::MqMessaging', 'mqtt' => 'iqrf::MqttMessaging',
			'udp' => 'iqrf::UdpMessaging', 'websocket' => 'iqrf::WebsocketMessaging'
		];
		$messagings = [];
		foreach ($components as $name => $component) {
			$messagings['config.' . $name . '.title'] = $this->getComponentInstances($component);
		}
		return $messagings;
	}

	/**
	 * Get available instances of component
	 * @param string $component Component
	 * @return array Available instances of component
	 */
	public function getComponentInstances(string $component): array {
		$instances = [];
		$this->setComponent($component);
		foreach ($this->getInstances() as $instance) {
			$instances[] = $instance['instance'];
		}
		return $instances;
	}

	/**
	 * Get an instance file name with the property
	 * @param string $type Property type
	 * @param mixed $value Property value
	 * @return string Instance file name
	 */
	public function getInstanceByProperty(string $type, $value) {
		$dir = $this->fileManager->getDirectory();
		foreach (Finder::findFiles('*.json')->exclude('config.json')->from($dir) as $file) {
			$fileName = Strings::replace($file->getRealPath(), ['~^' . realpath($dir) . '/~', '/.json$/'], '');
			$json = $this->fileManager->read($fileName);
			if (array_key_exists($type, $json) && $json[$type] === $value) {
				return $fileName;
			}
		}
	}

	/**
	 * Fix a required interfaces in the configuration
	 * @param array $configuration Configuration to fix
	 * @return array Configuration with a fixed required interfaces
	 */
	public function fixRequiredInterfaces(array $configuration) :array {
		if (!array_key_exists('RequiredInterfaces', $configuration)) {
			return $configuration;
		}
		$requiredInterfaces = $configuration['RequiredInterfaces'];
		foreach ($requiredInterfaces as $id => $requiredInterface) {
			if (!array_key_exists('instance', $requiredInterface['target'])) {
				$value = reset($requiredInterface['target']);
				$property = key($requiredInterface['target']);
				$instanceFileName = $this->getInstanceByProperty($property, $value);
				$instanceName = $this->fileManager->read($instanceFileName)['instance'];
				$configuration['RequiredInterfaces'][$id]['target']['instance'] = $instanceName;
			}
		}
		return $configuration;
	}

}
