<?php

/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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
use Nette\Utils\JsonException;
use Nette\Utils\Strings;

/**
 * Main configuration form factory
 */
class MainManager {

	/**
	 * @var FileManager JSON file manager
	 */
	private FileManager $fileManager;

	/**
	 * @var string File name
	 */
	private string $fileName = 'config.json';

	/**
	 * Constructor
	 * @param FileManager $fileManager JSON file manager
	 */
	public function __construct(FileManager $fileManager) {
		$this->fileManager = $fileManager;
	}

	/**
	 * Returns the path of cache directory
	 * @return string Cache directory path
	 */
	public function getCacheDir(): string {
		try {
			$dir = $this->load()['cacheDir'];
			return Strings::endsWith($dir, '/') ? $dir : $dir . '/';
		} catch (IOException | JsonException $e) {
			return '/var/cache/iqrf-gateway-daemon/';
		}
	}

	/**
	 * Returns the path of cache directory
	 * @return string Data directory path
	 */
	public function getDataDir(): string {
		try {
			$dir = $this->load()['dataDir'];
			return Strings::endsWith($dir, '/') ? $dir : $dir . '/';
		} catch (IOException | JsonException $e) {
			return '/usr/share/iqrf-gateway-daemon/';
		}
	}

	/**
	 * Converts the main configuration form array to JSON array
	 * @return array<string, array<array<string, bool|int|string>>|string> Array for form
	 * @throws JsonException
	 */
	public function load(): array {
		return $this->fileManager->readJson($this->fileName);
	}

	/**
	 * Saves the main daemon configuration
	 * @param array<string, array<array<string, bool|int|string>>|string> $array Main configuration
	 * @throws JsonException
	 */
	public function save(array $array): void {
		$json = (array) $this->fileManager->readJson($this->fileName);
		$this->fileManager->writeJson($this->fileName, array_merge($json, $array));
	}

}
