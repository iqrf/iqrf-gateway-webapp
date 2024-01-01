<?php

/**
 * TEST: App\NetworkModule\Entities\ConnectionDetail
 * @covers App\NetworkModule\Entities\ConnectionDetail
 * @phpVersion >= 7.4
 * @testCase
 */
/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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

namespace Tests\Unit\NetworkModule\Entities;

use App\NetworkModule\Entities\AutoConnect;
use App\NetworkModule\Utils\NmCliConnection;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for automatic connecting entity
 */
final class AutoConnectTest extends TestCase {

	/**
	 * Is automatic connecting enabled?
	 */
	private const ENABLED = true;

	/**
	 * Connection priority
	 */
	private const PRIORITY = 0;

	/**
	 * Connection retries
	 */
	private const RETRIES = -1;

	/**
	 * JSON serialized entity
	 */
	private const JSON = [
		'enabled' => self::ENABLED,
		'priority' => self::PRIORITY,
		'retries' => self::RETRIES,
	];

	/**
	 * @var AutoConnect Automatic connecting entity
	 */
	private AutoConnect $entity;

	/**
	 * Tests the function to deserialize the automatic connecting entity from JSON
	 */
	public function testJsonDeserialize(): void {
		Assert::equal($this->entity, AutoConnect::jsonDeserialize((object) self::JSON));
	}

	/**
	 * Tests the function to serialize the automatic connecting entity into JSON
	 */
	public function testJsonSerialize(): void {
		Assert::same(self::JSON, $this->entity->jsonSerialize());
	}

	/**
	 * Tests the function to deserialize the automatic connecting entity from nmcli configuration
	 */
	public function testNmCliDeserialize(): void {
		$nmCli = 'connection.autoconnect:yes' . PHP_EOL .
			'connection.autoconnect-priority:0' . PHP_EOL .
			'connection.autoconnect-retries:-1';
		$nmCli = NmCliConnection::decode($nmCli);
		Assert::equal($this->entity, AutoConnect::nmCliDeserialize($nmCli));
	}

	/**
	 * Tests the function to serialize the automatic connecting entity into nmcli configuration
	 */
	public function testNmCliSerialize(): void {
		$expected = 'connection.autoconnect "yes" connection.autoconnect-priority "0" connection.autoconnect-retries "-1" ';
		Assert::same($expected, $this->entity->nmCliSerialize());
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->entity = new AutoConnect(self::ENABLED, self::PRIORITY, self::RETRIES);
	}

}

$test = new AutoConnectTest();
$test->run();
