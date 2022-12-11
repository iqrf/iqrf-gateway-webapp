<?php

/**
 * TEST: App\NetworkModule\Entities\ConnectionDetail
 * @covers App\NetworkModule\Entities\ConnectionDetail
 * @phpVersion >= 7.4
 * @testCase
 */
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

namespace Tests\Unit\NetworkModule\Entities\WifiSecurity;

use App\NetworkModule\Entities\WifiSecurity\Leap;
use App\NetworkModule\Utils\NmCliConnection;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for Cisco LEAP entity
 */
final class LeapTest extends TestCase {

	/**
	 * @var string LEAP username
	 */
	private const USERNAME = 'name';

	/**
	 * @var string LEAP password
	 */
	private const PASSWORD = 'pass';

	/**
	 * @var array{username: string, password: string} JSON serialized entity
	 */
	private const JSON = [
		'username' => self::USERNAME,
		'password' => self::PASSWORD,
	];

	/**
	 * @var Leap Cisco LEAP entity
	 */
	private Leap $entity;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->entity = new Leap(self::USERNAME, self::PASSWORD);
	}

	/**
	 * Tests the function to deserialize the Cisco LEAP entity from JSON
	 */
	public function testJsonDeserialize(): void {
		Assert::equal($this->entity, Leap::jsonDeserialize((object) self::JSON));
	}

	/**
	 * Tests the function to serialize the Cisco LEAP entity into JSON
	 */
	public function testJsonSerialize(): void {
		Assert::same(self::JSON, $this->entity->jsonSerialize());
	}

	/**
	 * Tests the function to deserialize the Cisco LEAP entity from nmcli configuration
	 */
	public function testNmCliDeserialize(): void {
		$nmCli = '802-11-wireless-security.leap-password:pass' . PHP_EOL .
			'802-11-wireless-security.leap-username:name';
		$nmCli = NmCliConnection::decode($nmCli);
		Assert::equal($this->entity, Leap::nmCliDeserialize($nmCli));
	}

	/**
	 * Tests the function to serialize the Cisco LEAP entity into nmcli configuration
	 */
	public function testNmCliSerialize(): void {
		$expected = '802-11-wireless-security.leap-password "pass" 802-11-wireless-security.leap-username "name" ';
		Assert::same($expected, $this->entity->nmCliSerialize());
	}

}

$test = new LeapTest();
$test->run();
