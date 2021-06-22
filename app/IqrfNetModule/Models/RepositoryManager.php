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

namespace App\IqrfNetModule\Models;

use App\IqrfNetModule\Exceptions\RepositoryConfigMissingException;
use Nette\Neon\Neon;
use Nette\Utils\FileSystem;

/**
 * IQRF Repository manager
 */
class RepositoryManager {

	/**
	 * @var string Path to configuration file
	 */
	private $confPath;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->confPath = __DIR__ . '/../../config/config.neon';
	}

	/**
	 * Reads and returns IQRF repository configuration
	 * @return array<mixed> IQRF repository configuration
	 */
	public function getConfig(): array {
		$config = Neon::decode($this->readConfig());
		if (array_key_exists('iqrfRepository', $config)) {
			return $config['iqrfRepository'];
		}
		throw new RepositoryConfigMissingException('IQRF repository configuration does not exist.');
	}

	/**
	 * Saves IQRF repository configuration
	 * @param array<string, string|array<string, string>> $config IQRF repository configuration to save
	 */
	public function saveConfig(array $config): void {
		$oldConfig = Neon::decode($this->readConfig());
		$oldConfig['iqrfRepository'] = $config;
		FileSystem::write($this->confPath, Neon::encode($oldConfig, Neon::BLOCK));
	}

	/**
	 * Reads config file and returns content
	 * @return string Config file content
	 */
	private function readConfig(): string {
		return FileSystem::read($this->confPath);
	}

}
