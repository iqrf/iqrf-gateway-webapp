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

use Nette\IOException;
use Nette\Neon\Exception as NeonException;
use Nette\Neon\Neon;
use Nette\Utils\FileSystem;

/**
 * IQRF Repository manager
 */
class IqrfRepositoryManager {

	/**
	 * Default configuration
	 */
	private const DEFAULT_CONFIG = [
		'apiEndpoint' => 'https://repository.iqrfalliance.org/api',
		'credentials' => [
			'username' => null,
			'password' => null,
		],
	];

	/**
	 * Extension name
	 */
	private const EXTENSION_NAME = 'iqrfRepository';

	/**
	 * @var string Path to configuration file
	 */
	private string $confPath;

	/**
	 * Constructor
	 */
	public function __construct(string $path) {
		$this->confPath = $path;
	}

	/**
	 * Saves IQRF repository configuration
	 * @param array<string, array<string, string|null>|string> $config IQRF repository configuration to save
	 * @throws IOException
	 * @throws NeonException
	 */
	public function saveConfig(array $config): void {
		FileSystem::write($this->confPath, Neon::encode([self::EXTENSION_NAME => $config], Neon::BLOCK));
	}

	/**
	 * Reads and returns IQRF repository configuration
	 * @return array<string, array<string, string|null>|string> IQRF Repository integration configuration
	 */
	public function readConfig(): array {
		try {
			$content = Neon::decode(FileSystem::read($this->confPath))[self::EXTENSION_NAME] ?? [];
			return array_replace_recursive(self::DEFAULT_CONFIG, $content);
		} catch (IOException | NeonException $e) {
			return self::DEFAULT_CONFIG;
		}
	}

}
