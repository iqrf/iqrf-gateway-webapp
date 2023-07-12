<?php

/**
 * TEST: App\NetworkModule\Entities\GSMConnection
 * @covers App\NetworkModule\Entities\GSMConnection
 * @phpVersion >= 7.4
 * @testCase
 */
/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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

use App\NetworkModule\Entities\GSMConnection;
use App\NetworkModule\Utils\NmCliConnection;
use Nette\Utils\ArrayHash;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for GSM connection entity
 */
final class GSMConnectionTest extends TestCase {

	/**
	 * NetworkManager data directory
	 */
	private const NM_DATA = TESTER_DIR . '/data/networkManager/';

	/**
	 * GSM APN
	 */
	private const APN = 'internet';

	/**
	 * Username
	 */
	private const USERNAME = 'testuser';

	/**
	 * Password
	 */
	private const PASSWORD = 'testpass';

	/**
	 * SIM PIN
	 */
	private const PIN = '1234';

	/**
	 * @var GSMConnection GSM connection entity
	 */
	private GSMConnection $entity;

	/**
	 * @var GSMConnection GSM connection entity with null optional parameters
	 */
	private GSMConnection $nullEntity;

	/**
	 * Tests the function to deserialize JSON into GSM connection entity
	 */
	public function testJsonDeserialize(): void {
		$connection = ArrayHash::from([
			'apn' => self::APN,
			'username' => self::USERNAME,
			'password' => self::PASSWORD,
			'pin' => self::PIN,
		], true);
		Assert::equal($this->entity, GSMConnection::jsonDeserialize($connection));
		$connection->username = $connection->password = $connection->pin = null;
		Assert::equal($this->nullEntity, GSMConnection::jsonDeserialize($connection));
	}

	/**
	 * Tests the function to serialize GSM connection entity into JSON
	 */
	public function testJsonSerialize(): void {
		$expected = [
			'apn' => self::APN,
			'username' => self::USERNAME,
			'password' => self::PASSWORD,
			'pin' => self::PIN,
		];
		Assert::same($expected, $this->entity->jsonSerialize());
		$expected['username'] = $expected['password'] = $expected['pin'] = null;
		Assert::same($expected, $this->nullEntity->jsonSerialize());
	}

	/**
	 * Tests the function to create a new GSM connection entity from nmcli connection configuration
	 */
	public function testNmCliDeserialize(): void {
		$connection = FileSystem::read(self::NM_DATA . 'db948952-8f91-47c8-89fa-cda6cc9bc55a.conf');
		$connection = NmCliConnection::decode($connection);
		Assert::equal($this->entity, GSMConnection::nmCliDeserialize($connection));
	}

	/**
	 * Tests the function to serialize GSM connection entity to nmcli connection configuration
	 */
	public function testNmCliSerialize(): void {
		$expected = sprintf('gsm.apn "%s" gsm.username "%s" gsm.password "%s" gsm.pin "%s" ', self::APN, self::USERNAME, self::PASSWORD, self::PIN);
		Assert::same($expected, $this->entity->nmCliSerialize());
		$expected = sprintf('gsm.apn "%s" gsm.username "" gsm.password "" gsm.pin "" ', self::APN);
		Assert::same($expected, $this->nullEntity->nmCliSerialize());
	}

	/**
	 * Sets up the testing environment
	 */
	protected function setUp(): void {
		$this->entity = new GSMConnection(self::APN, self::USERNAME, self::PASSWORD, self::PIN);
		$this->nullEntity = new GSMConnection(self::APN);
	}

}

$test = new GSMConnectionTest();
$test->run();
