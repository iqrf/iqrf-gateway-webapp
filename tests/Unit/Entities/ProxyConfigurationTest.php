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

namespace Tests\Unit\Entities;

use App\Entities\ProxyConfiguration;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for ProxyConfiguration entity
 */
final class ProxyConfigurationTest extends TestCase {

	/**
	 * JSON-serialized proxy configuration
	 */
	private const JSON_CONFIG = [
		'host' => ProxyConfiguration::DEFAULT_HOST,
		'port' => ProxyConfiguration::DEFAULT_PORT,
		'address' => ProxyConfiguration::DEFAULT_ADDRESS,
		'upstream' => ProxyConfiguration::DEFAULT_UPSTREAM,
		'token' => '',
	];

	/**
	 * @var ProxyConfiguration Proxy configuration object
	 */
	private ProxyConfiguration $config;

	/**
	 * Tests the function to merge incomplete proxy configuration with defaults
	 */
	public function testMergeDefaults(): void {
		Assert::equal(
			expected: $this->config,
			actual: ProxyConfiguration::mergeDefaults([]),
		);
	}

	/**
	 * Tests the function to deserialize proxy configuration into object
	 */
	public function testJsonDeserialize(): void {
		Assert::equal(
			expected: $this->config,
			actual: ProxyConfiguration::jsonDeserialize(self::JSON_CONFIG),
		);
	}

	/**
	 * Tests the function to serialize proxy configuration object into json
	 */
	public function testJsonSerialize(): void {
		Assert::same(
			expected: self::JSON_CONFIG,
			actual: $this->config->jsonSerialize(),
		);
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->config = new ProxyConfiguration(
			host: ProxyConfiguration::DEFAULT_HOST,
			port: ProxyConfiguration::DEFAULT_PORT,
			address: ProxyConfiguration::DEFAULT_ADDRESS,
			upstream: ProxyConfiguration::DEFAULT_UPSTREAM,
		);
	}

}

$test = new ProxyConfigurationTest();
$test->run();
