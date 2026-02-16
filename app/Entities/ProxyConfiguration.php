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

namespace App\Entities;

use JsonSerializable;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;
use Nette\Schema\Processor;

/**
 * Proxy configuration class
 */
class ProxyConfiguration implements JsonSerializable {

	/**
	 * Default (fallback) proxy server host
	 */
	public const DEFAULT_HOST = 'localhost';

	/**
	 * Default (fallback) proxy server port
	 */
	public const DEFAULT_PORT = 9000;

	/**
	 * Default (fallback) binding address
	 */
	public const DEFAULT_ADDRESS = '127.0.0.1';

	/**
	 * Default (fallback) upstream URL
	 */
	public const DEFAULT_UPSTREAM = 'ws://localhost:1338';

	/**
	 * Constructs a proxy configuration object
	 *
	 * @param string $host Proxy HTTP host
	 * @param int $port Proxy port
	 * @param string $address Bind address
	 * @param string $upstream Upstream address
	 * @param string $token Upstream authentication token
	 */
	public function __construct(
		public string $host = 'localhost',
		public int $port = 9000,
		public string $address = '127.0.0.1',
		public string $upstream = 'ws://localhost:1338',
		public string $token = '',
	) {
	}

	/**
	 * Merges proxy configuration with default values
	 * @param array{
	 *  host: string,
	 *  port: int,
	 *  address: string,
	 *  upstream: string,
	 *  token: string,
	 * }|ProxyConfiguration $config Proxy configuration
	 * @return self Merged proxy configuration
	 */
	public static function mergeDefaults(array|self $config): self {
		return self::jsonDeserialize(
			(new Processor())->process(self::createValidationSchema(), $config),
		);
	}

	/**
	 * Deserializes proxy configuration from JSON
	 * @param array{
	 *  host: string,
	 *  port: int,
	 *  address: string,
	 *  upstream: string,
	 *  token: string,
	 * } $json JSON serialized proxy configuration
	 * @return self Proxy configuration
	 */
	public static function jsonDeserialize(array $json): self {
		return new self(
			$json['host'],
			$json['port'],
			$json['address'],
			$json['upstream'],
			$json['token'],
		);
	}

	/**
	 * Creates a proxy configuration validation schema
	 * @return Structure Proxy configuration validation schema
	 */
	public static function createValidationSchema(): Structure {
		return Expect::structure([
			'host' => Expect::string(self::DEFAULT_HOST),
			'port' => Expect::int(self::DEFAULT_PORT),
			'address' => Expect::string(self::DEFAULT_ADDRESS),
			'upstream' => Expect::string(self::DEFAULT_UPSTREAM),
			'token' => Expect::string(''),
		])->castTo('array');
	}

	/**
	 * Serializes proxy configuration into JSON
	 * @return array{
	 *  host: string,
	 *  port: int,
	 *  address: string,
	 *  upstream: string,
	 *  token: string,
	 * } JSON-serialized proxy configuration
	 */
	public function jsonSerialize(): array {
		return [
			'host' => $this->host,
			'port' => $this->port,
			'address' => $this->address,
			'upstream' => $this->upstream,
			//TODO obfuscate token
			'token' => $this->token,
		];
	}

}
