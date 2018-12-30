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

namespace App\ConfigModule\Models;

use App\CoreModule\Models\JsonFileManager;
use Nette\SmartObject;
use Nette\Utils\Arrays;
use Nette\Utils\JsonException;

/**
 * Component configuration manager
 */
class ComponentManager implements IConfigManager {

	use SmartObject;

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
	 * Add new component
	 * @param mixed[] $array Component's settings
	 * @throws JsonException
	 */
	public function add(array $array): void {
		$json = $this->fileManager->read($this->fileName);
		$json['components'][] = $array;
		$this->fileManager->write($this->fileName, $json);
	}

	/**
	 * Delete a component
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
	 * Load a component from main configuration JSON
	 * @param int $id Component ID
	 * @return mixed[] Array for form
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
	 * Load components from main configuration JSON
	 * @return mixed[] Array for form
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
	 * Save components setting
	 * @param mixed[] $components Components settings
	 * @param int $id Component ID
	 * @throws JsonException
	 */
	public function save(array $components, int $id): void {
		$json = $this->fileManager->read($this->fileName);
		$json['components'][$id] = $components;
		$this->fileManager->write($this->fileName, $json);
	}

}
