<?php

/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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

use App\CoreModule\Models\FileManager;
use Nette\IOException;
use Nette\Utils\Arrays;
use Nette\Utils\Finder;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;

/**
 * Generic configuration form factory
 */
class GenericManager {

	/**
	 * @var string Component type
	 */
	private string $component;

	/**
	 * @var FileManager JSON file manager
	 */
	private FileManager $fileManager;

	/**
	 * @var string|null File name (without .json)
	 */
	private ?string $fileName = null;

	/**
	 * @var ComponentSchemaManager JSON schema manager
	 */
	private ComponentSchemaManager $schemaManager;

	/**
	 * Constructor
	 * @param FileManager $fileManager JSON file manager
	 * @param ComponentSchemaManager $schemaManager JSON schema manager
	 */
	public function __construct(FileManager $fileManager, ComponentSchemaManager $schemaManager) {
		$this->fileManager = $fileManager;
		$this->schemaManager = $schemaManager;
	}

	/**
	 * Deletes a configuration
	 * @param string|null $fileName File name
	 * @throws IOException
	 */
	public function deleteFile(?string $fileName = null): void {
		if ($fileName === null) {
			return;
		}
		$this->fileManager->delete($fileName . '.json');
	}

	/**
	 * Returns instance configuration file name
	 * @param string $instance Instance name
	 * @return string|null Instance file name
	 */
	public function getInstanceFileName(string $instance): ?string {
		$files = $this->getInstanceFiles();
		foreach ($files as $fileName) {
			$configuration = $this->read($fileName);
			if ($configuration['instance'] === $instance) {
				return $fileName;
			}
		}
		return null;
	}

	/**
	 * Gets component's instance files
	 * @return array<string> Files with component's instances
	 */
	public function getInstanceFiles(): array {
		$dir = $this->fileManager->getBasePath();
		$instances = [];
		foreach (Finder::findFiles('*.json')->exclude('config.json')->in($dir) as $file) {
			$fileName = Strings::replace($file->getRealPath(), ['~^' . realpath($dir) . '/~', '/.json$/'], '');
			try {
				$json = $this->fileManager->readJson($fileName . '.json');
			} catch (IOException | JsonException $e) {
				continue;
			}
			if (array_key_exists('component', $json) && $json['component'] === $this->component) {
				$instances[] = $fileName;
			}
		}
		sort($instances);
		return $instances;
	}

	/**
	 * Get a list of configurations
	 * @return array<mixed> Configurations
	 */
	public function list(): array {
		$instances = [];
		foreach ($this->getInstanceFiles() as $fileName) {
			try {
				$instances[] = $this->read($fileName);
			} catch (IOException | JsonException $e) {
				continue;
			}
		}
		return $instances;
	}

	/**
	 * Loads the configuration
	 * @param string $instance Instance name
	 * @return array<mixed> Configuration in an array
	 * @throws JsonException
	 */
	public function loadInstance(string $instance): array {
		$fileName = $this->getInstanceFileName($instance);
		if ($fileName === null) {
			return [];
		}
		return $this->read($fileName);
	}

	/**
	 * Fixes the required interfaces in the configuration
	 * @param array<mixed> $configuration Configuration to fix
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
				$property = strval(array_key_first($requiredInterface['target']));
				$instanceFileName = $this->getInstanceByProperty($property, $value);
				if ($instanceFileName === null) {
					unset($configuration['RequiredInterfaces'][$id]);
					continue;
				}
				$instanceName = $this->fileManager->readJson($instanceFileName . '.json')['instance'];
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
	 */
	public function getInstanceByProperty(string $type, $value): ?string {
		$dir = $this->fileManager->getBasePath();
		foreach (Finder::findFiles('*.json')->exclude('config.json')->in($dir) as $file) {
			$fileName = Strings::replace($file->getRealPath(), ['~^' . realpath($dir) . '/~', '/.json$/'], '');
			try {
				$json = $this->fileManager->readJson($fileName . '.json');
			} catch (IOException | JsonException $e) {
				continue;
			}
			if (array_key_exists($type, $json) && $json[$type] === $value) {
				return $fileName;
			}
		}
		return null;
	}

	/**
	 * Reads the configuration
	 * @param string|null $fileName File name
	 * @return array<mixed> Configuration in an array
	 * @throws IOException
	 * @throws JsonException
	 */
	public function read(?string $fileName = null): array {
		if ($fileName === null && $this->fileName === null) {
			return [];
		}
		if ($fileName === null) {
			$fileName = $this->fileName;
		}
		$configuration = $this->fileManager->readJson($fileName . '.json');
		$this->fixRequiredInterfaces($configuration);
		return $configuration;
	}

	/**
	 * Saves the configuration
	 * @param array<mixed> $array Configuration in an array
	 * @param string|null $fileName File name
	 * @throws IOException
	 * @throws JsonException
	 */
	public function save(array $array, ?string $fileName = null): void {
		if ($fileName === null && $this->fileName === null) {
			$fileName = $this->generateFileName($array);
		}
		if ($fileName === null) {
			$fileName = $this->fileName;
		}
		$component = ['component' => $this->component];
		$configuration = Arrays::mergeTree($component, $array);
		$json = Json::encode($configuration);
		$this->schemaManager->validate(Json::decode($json));
		$this->fileManager->writeJson($fileName . '.json', $configuration);
	}

	/**
	 * Generates a configuration file name
	 * @param array<mixed> $array Configuration from form
	 * @return string Generated file name
	 */
	public function generateFileName(array $array): string {
		$prefix = Strings::replace($this->component, '#::#', '__');
		return $prefix . '__' . $array['instance'];
	}

	/**
	 * Sets the component type
	 * @param string $component Component name
	 */
	public function setComponent(string $component): void {
		$this->component = $component;
		$this->schemaManager->setSchema($component);
	}

}
