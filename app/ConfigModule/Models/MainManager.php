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
use Nette\Utils\JsonException;

/**
 * Main configuration form factory
 */
class MainManager {

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
	 * Returns the path of cache directory
	 * @return string Cache directory path
	 */
	public function getCacheDir(): string {
		try {
			return $this->load()['cacheDir'];
		} catch (IOException | JsonException $e) {
			return '/var/cache/iqrf-gateway-daemon/';
		}
	}

	/**
	 * Converts the main configuration form array to JSON array
	 * @return mixed[] Array for form
	 * @throws JsonException
	 */
	public function load(): array {
		return $this->fileManager->read($this->fileName);
	}

	/**
	 * Saves the main daemon configuration
	 * @param mixed[] $array Main configuration
	 * @throws JsonException
	 */
	public function save(array $array): void {
		$json = (array) $this->fileManager->read($this->fileName);
		$this->fileManager->write($this->fileName, array_merge($json, $array));
	}

}
