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
use Nette\IOException;
use Nette\Utils\Arrays;
use Nette\Utils\JsonException;

/**
 * Component configuration manager
 */
class ComponentManager implements IConfigManager {

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManager;

	/**
	 * @var string File name (without .json)
	 */
	private $fileName = 'config';

	/**
	 * Constructor
	 * @param JsonFileManager $fileManager JSON file manager
	 */
	public function __construct(JsonFileManager $fileManager) {
		$this->fileManager = $fileManager;
	}

	/**
	 * Adds a new component
	 * @param array<mixed> $array Component's settings
	 * @throws JsonException
	 */
	public function add(array $array): void {
		$json = $this->fileManager->read($this->fileName);
		$json['components'][] = $array;
		$this->fileManager->write($this->fileName, $json);
	}

	/**
	 * Deletes the component
	 * @param int $id Component ID
	 * @throws JsonException
	 */
	public function delete(int $id): void {
		$json = $this->fileManager->read($this->fileName);
		unset($json['components'][$id]);
		$json['components'] = array_values($json['components']);
		$this->fileManager->write($this->fileName, $json);
	}

	/**
	 * Returns component ID
	 * @param string $name Components name
	 * @return int|null Component ID
	 */
	public function getId(string $name): ?int {
		try {
			$json = $this->fileManager->read($this->fileName);
		} catch (JsonException $e) {
			return null;
		}
		$search = array_search($name, array_column($json['components'], 'name'), true);
		return $search !== false ? $search : null;
	}

	/**
	 * Loads the component from main configuration JSON
	 * @param int $id Component ID
	 * @return array<mixed> Array for form
	 * @throws JsonException
	 */
	public function load(int $id): array {
		$json = $this->fileManager->read($this->fileName)['components'];
		if (array_key_exists($id, $json)) {
			return $json[$id];
		}
		return [];
	}

	/**
	 * Loads components from main configuration JSON
	 * @return array<array<string, bool|int|string>> Array for form
	 * @throws JsonException
	 */
	public function list(): array {
		$components = [];
		$json = $this->fileManager->read($this->fileName)['components'];
		foreach ($json as $id => $config) {
			$components[$id] = Arrays::mergeTree(['id' => $id], $config);
		}
		return $components;
	}

	/**
	 * Returns disabled components
	 * @return array<string, bool>|null Disabled components
	 */
	public function listDisabled(): ?array {
		$disabled = [];
		try {
			$components = $this->fileManager->read($this->fileName)['components'];
			foreach ($components as $component) {
				if ($component['enabled'] !== true) {
					$disabled[$component['name']] = false;
				}
			}
		} catch (IOException | JsonException $e) {
			return null;
		}
		return $disabled;
	}

	/**
	 * Saves the component's configuration
	 * @param array<mixed> $components Component's configuration
	 * @param int $id Component ID
	 * @throws JsonException
	 */
	public function save(array $components, int $id): void {
		$json = $this->fileManager->read($this->fileName);
		$json['components'][$id] = $components;
		$this->fileManager->write($this->fileName, $json);
	}

}
