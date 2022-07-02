<?php

/**
 * Copyright 2017-2022 IQRF Tech s.r.o.
 * Copyright 2019-2022 MICRORISC s.r.o.
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

namespace App\GatewayModule\Models\Utils;

use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\JsonFileManager;

class GatewayInfoUtil {

	/**
	 * Path to directory containing gateway file
	 */
	private const DIR = '/etc/';

	/**
	 * Gateway file name
	 */
	private const FILE_NAME = 'iqrf-gateway';

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManager;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 */
	public function __construct(CommandManager $commandManager) {
		$this->fileManager = new JsonFileManager(self::DIR, $commandManager);
	}

	/**
	 * Returns gateway ID if gateway file exists
	 * @param string $property Gateway property name
	 * @return string Gateway property value
	 */
	public function getProperty(string $property): ?string {
		$json = $this->readGatewayFile();
		if (array_key_exists($property, $json)) {
			return $json[$property];
		}
		return null;
	}

	/**
	 * Checks if gateway file exists
	 * @return bool true if gateway file exists, false otherwise
	 */
	private function exists(): bool {
		return $this->fileManager->exists(self::FILE_NAME);
	}

	/**
	 * Returns gateway configuration if the file exists
	 * @return array<mixed> Gateway configuration
	 */
	private function readGatewayFile(): array {
		if (!$this->exists()) {
			return [];
		}
		return $this->fileManager->read(self::FILE_NAME);
	}

}
