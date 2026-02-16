<?php

/**
 * Copyright 2017-2026 IQRF Tech s.r.o.
 * Copyright 2019-2026 MICRORISC s.r.o.
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

namespace App\Models\WebSocket;

use App\Entities\ProxyConfiguration;
use Iqrf\FileManager\FileManager;
use Nette\IOException;
use Nette\Neon\Exception as NeonException;

/**
 * Proxy configuration manager
 */
class ProxyConfigManager {

	/**
	 * WebSocket proxy configuration file
	 */
	private const FILE_NAME = 'proxy-config.neon';

	/**
	 * Constructor
	 * @param FileManager $fileManager File manager
	 */
	public function __construct(
		private readonly FileManager $fileManager,
	) {
	}

	/**
	 * Returns proxy configuration
	 * @return ProxyConfiguration Proxy configuration
	 */
	public function readConfig(): ProxyConfiguration {
		try {
			$config = $this->fileManager->readNeon(self::FILE_NAME);
		} catch (IOException | NeonException) {
			$config = [];
		}
		return ProxyConfiguration::mergeDefaults($config);
	}

	/**
	 * Saves proxy configuration
	 * @param ProxyConfiguration $config Proxy configuration
	 */
	public function writeConfig(ProxyConfiguration $config): void {
		$this->fileManager->writeNeon(self::FILE_NAME, $config->jsonSerialize());
	}

}
