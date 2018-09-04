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

use App\CoreModule\Model\JsonFileManager;
use App\CoreModule\Model\JsonSchemaManager;
use Nette;
use Nette\Utils\Arrays;
use Nette\Utils\ArrayHash;
use Nette\Utils\Finder;
use Nette\Utils\Strings;

/**
 * Generic configuration form factory
 */
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
	 * @param int $id Configuration ID
	 */
	public function delete(int $id): void {
		$instanceFiles = $this->getInstanceFiles();
		if (isset($instanceFiles[$id])) {
			$this->fileManager->delete($instanceFiles[$id]);
		}
	}

	/**
	 * Load a configuration
	 * @param int $id Configuration ID
	 * @return array Configuration in an array
	 */
	public function load(int $id): array {
		$instanceFiles = $this->getInstanceFiles();
		if (!isset($instanceFiles[$id])) {
			return [];
		}
		$configuration = $this->fileManager->read($instanceFiles[$id]);
		$this->fixRequiredInterfaces($configuration);
		return $configuration;
	}

	/**
	 * List configurations
	 * @return array Configurations
	 */
	public function list(): array {
		$files = array_keys($this->getInstanceFiles());
		$instances = [];
		foreach ($files as $id) {
			$instances[] = Arrays::mergeTree(['id' => $id], $this->load($id));
		}
		return $instances;
	}

	/**
	 * Save configuration
	 * @param array $array Settings
	 */
	public function save(array $array): void {
		if (!isset($this->fileName)) {
			$this->generateFileName($array);
		}
		$component = ['component' => $this->component];
		$configuration = Arrays::mergeTree($component, $array);
		$this->schemaManager->validate(ArrayHash::from($configuration));
		$this->fileManager->write($this->fileName, $configuration);
	}

	/**
	 * Set component type
	 * @param string $component Component name
	 */
	public function setComponent(string $component): void {
		$this->component = $component;
		$this->schemaManager->setSchemaFromComponent($component);
	}

	/**
	 * Get file name
	 * @return string File name (without .json)
	 */
	public function getFileName(): string {
		return $this->fileName;
	}

	/**
	 * Set file name
	 * @param string $fileName File name (without .json)
	 */
	public function setFileName(string $fileName): void {
		$this->fileName = $fileName;
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
		$files = array_keys($this->getInstanceFiles());
		foreach ($files as $id) {
			$instance = $this->load($id);
			$instances[] = $instance['instance'];
		}
		return $instances;
	}

	/**
	 * Get an instance file name with the property
	 * @param string $type Property type
	 * @param mixed $value Property value
	 * @return string|null Instance file name
	 */
	public function getInstanceByProperty(string $type, $value): ?string {
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
	 */
	public function fixRequiredInterfaces(array &$configuration): void {
		if (!array_key_exists('RequiredInterfaces', $configuration)) {
			return;
		}
		$requiredInterfaces = $configuration['RequiredInterfaces'];
		foreach ($requiredInterfaces as $id => $requiredInterface) {
			if (!array_key_exists('instance', $requiredInterface['target'])) {
				$value = reset($requiredInterface['target']);
				$property = strval(key($requiredInterface['target']));
				$instanceFileName = $this->getInstanceByProperty($property, $value);
				$instanceName = $this->fileManager->read($instanceFileName)['instance'];
				unset($configuration['RequiredInterfaces'][$id]['target']);
				$configuration['RequiredInterfaces'][$id]['target']['instance'] = $instanceName;
			}
		}
	}

	/**
	 * Generate a configuration file name
	 * @param array $array Configuration from form
	 */
	public function generateFileName(array $array): void {
		$prefix = explode('::', $this->component)[0];
		$this->fileName = $prefix . '__' . $array['instance'];
	}

}
