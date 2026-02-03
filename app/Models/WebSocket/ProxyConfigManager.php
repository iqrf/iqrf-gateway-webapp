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

use Iqrf\FileManager\FileManager;
use Nette\IOException;
use Nette\Neon\Exception as NeonException;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;
use Nette\Schema\Processor;

/**
 * Proxy configuration manager
 */
class ProxyConfigManager {

	/**
	 * Default (fallback) proxy server host
	 */
	public const DEFAULT_HOST = 'localhost';

	/**
	 * Default (fallback) proxy server port
	 */
	public const DEFAULT_PORT = 9000;

	/**
	 * Default (fallback) upstream URL
	 */
	public const DEFAULT_UPSTREAM = 'ws://localhost:1338';

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
	 * Creates a proxy configuration validation schema
	 * @return Structure Proxy configuration validation schema
	 */
	public static function createValidationSchema(): Structure {
		return Expect::structure([
			'host' => Expect::string(self::DEFAULT_HOST),
			'port' => Expect::int(self::DEFAULT_PORT),
			'upstream' => Expect::string(self::DEFAULT_UPSTREAM),
			'token' => Expect::string(''),
		])->castTo('array');
	}

	/**
	 * Returns proxy server configuration
	 * @return array{
	 *  host: string,
	 *  port: int,
	 *  upstream: string,
	 *  token: string,
	 * } Proxy server configuration
	 */
	public function readConfig(): array {
		try {
			$config = $this->fileManager->readNeon(self::FILE_NAME);
		} catch (IOException | NeonException) {
			$config = [];
		}
		$processor = new Processor();
		return $processor->process(self::createValidationSchema(), $config);
	}

}
