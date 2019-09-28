<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2019 IQRF Tech s.r.o.
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
use App\CoreModule\Models\JsonSchemaManager;
use Nette\IOException;
use Nette\SmartObject;
use Nette\Utils\Arrays;
use Nette\Utils\Finder;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;

/**
 * Generic configuration form factory
 */
class GenericManager {

	use SmartObject;

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
	 * Deletes a configuration
	 * @param int $id Configuration ID
	 * @throws IOException
	 * @throws JsonException
	 */
	public function delete(int $id): void {
		$instanceFiles = $this->getInstanceFiles();
		if (isset($instanceFiles[$id])) {
			$this->fileManager->delete($instanceFiles[$id]);
		}
	}

	/**
	 * Deletes a configuration
	 * @throws IOException
	 */
	public function deleteFile(): void {
		$this->fileManager->delete($this->fileName);
	}

	/**
	 * Gets component's instance files
	 * @return string[] Files with component's instances
	 * @throws IOException
	 * @throws JsonException
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
		sort($instances);
		return $instances;
	}

	/**
	 * Get a list of  configurations
	 * @return mixed[] Configurations
	 * @throws IOException
	 * @throws JsonException
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
	 * Loads the configuration
	 * @param int $id Configuration ID
	 * @return mixed[] Configuration in an array
	 * @throws IOException
	 * @throws JsonException
	 */
	public function load(int $id): array {
		$instanceFiles = $this->getInstanceFiles();
		if (!isset($instanceFiles[$id])) {
			return [];
		}
		$this->fileName = $instanceFiles[$id];
		return $this->read();
	}

	/**
	 * Fixes the required interfaces in the configuration
	 * @param mixed[] $configuration Configuration to fix
	 * @throws IOException
	 * @throws JsonException
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
	 * Gets an instance file name with the property
	 * @param string $type Property type
	 * @param mixed $value Property value
	 * @return string|null Instance file name
	 * @throws IOException
	 * @throws JsonException
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
		return null;
	}

	/**
	 * Reads the configuration
	 * @return mixed[] Configuration in an array
	 * @throws JsonException
	 */
	public function read(): array {
		$configuration = $this->fileManager->read($this->fileName);
		$this->fixRequiredInterfaces($configuration);
		return $configuration;
	}

	/**
	 * Saves the configuration
	 * @param mixed[] $array Configuration in an array
	 * @throws IOException
	 * @throws JsonException
	 */
	public function save(array $array): void {
		if (!isset($this->fileName)) {
			$this->generateFileName($array);
		}
		$component = ['component' => $this->component];
		$configuration = Arrays::mergeTree($component, $array);
		$json = Json::encode($configuration);
		$this->schemaManager->validate(Json::decode($json));
		$this->fileManager->write($this->fileName, $configuration);
	}

	/**
	 * Generates a configuration file name
	 * @param mixed[] $array Configuration from form
	 */
	public function generateFileName(array $array): void {
		$prefix = explode('::', $this->component)[0];
		$this->fileName = $prefix . '__' . $array['instance'];
	}

	/**
	 * Gets the file name
	 * @return string File name (without .json)
	 */
	public function getFileName(): string {
		return $this->fileName;
	}

	/**
	 * Sets the file name
	 * @param string $fileName File name (without .json)
	 */
	public function setFileName(string $fileName): void {
		$this->fileName = $fileName;
	}

	/**
	 * Gets available messagings
	 * @return string[][] Available messagings
	 * @throws JsonException
	 */
	public function getMessagings(): array {
		$components = [
			'mq' => 'iqrf::MqMessaging',
			'mqtt' => 'iqrf::MqttMessaging',
			'udp' => 'iqrf::UdpMessaging',
			'websocket' => 'iqrf::WebsocketMessaging',
		];
		$messagings = [];
		foreach ($components as $name => $component) {
			$messagings['config.' . $name . '.title'] = $this->getComponentInstances($component);
		}
		return $messagings;
	}

	/**
	 * Gets available instances of component
	 * @param string $component Component
	 * @return string[] Available instances of component
	 * @throws JsonException
	 */
	public function getComponentInstances(string $component): array {
		$instances = [];
		$this->setComponent($component);
		$files = array_keys($this->getInstanceFiles());
		foreach ($files as $id) {
			$instance = $this->load($id);
			$instances[] = $instance['instance'];
		}
		sort($instances);
		return $instances;
	}

	/**
	 * Sets the component type
	 * @param string $component Component name
	 */
	public function setComponent(string $component): void {
		$this->component = $component;
		$this->schemaManager->setSchemaFromComponent($component);
	}

}
