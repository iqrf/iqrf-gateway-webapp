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

namespace App\MaintenanceModule\Models;

use App\CoreModule\Models\JsonFileManager;

/**
 * Mender client configuration manager
 */
class MenderManager {

	/**
	 * @var JsonFileManager $fileManager JSON file manager
	 */
	private $fileManager;

	/**
	 * JSON file containing mender-client configuration
	 */
	private const CLIENT_CONF = 'mender';

	/**
	 * JSON file containing mender-connect configuration
	 */
	private const CONNECT_CONF = 'mender-connect';

	/**
	 * Constructior
	 * @param JsonFileManager $fileManager JSON file manager
	 */
	public function __construct(JsonFileManager $fileManager) {
		$this->fileManager = $fileManager;
	}

	/**
	 * Reads JSON configuration file
	 * @return array<string, int|string> current Mender configuration
	 */
	public function getConfig(): array {
		return $this->fileManager->read(self::CLIENT_CONF, true, '.conf');
	}

	/**
	 * Saves updated mender-client configuration file
	 * @param array<string, int|string> $newConfig Mender configuration
	 */
	public function saveConfig(array $newConfig): void {
		$oldConfig = $this->getConfig();
		$this->fileManager->write(self::CLIENT_CONF, array_merge($oldConfig, $newConfig), '.conf');
		$this->updateConnectConfig($newConfig['ServerURL']);
	}

	/**
	 * Updates mender-connect configuration
	 * @param string $server mender-connect server url
	 */
	private function updateConnectConfig(string $server): void {
		$config = $this->fileManager->read(self::CONNECT_CONF, true, '.conf');
		$config['ServerURL'] = $server;
		$this->fileManager->write(self::CONNECT_CONF, $config, '.conf');
	}

}
