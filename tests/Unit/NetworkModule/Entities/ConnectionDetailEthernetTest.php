<?php

/**
 * TEST: App\NetworkModule\Entities\ConnectionDetail
 * @covers App\NetworkModule\Entities\ConnectionDetail
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

use App\NetworkModule\Entities\AutoConnect;
use App\NetworkModule\Entities\ConnectionDetail;
use App\NetworkModule\Entities\IPv4Address;
use App\NetworkModule\Entities\IPv4Connection;
use App\NetworkModule\Entities\IPv6Address;
use App\NetworkModule\Entities\IPv6Connection;
use App\NetworkModule\Enums\ConnectionTypes;
use App\NetworkModule\Enums\IPv4Methods;
use App\NetworkModule\Enums\IPv6Methods;
use App\NetworkModule\Utils\NmCliConnection;
use Darsyn\IP\Version\IPv4;
use Darsyn\IP\Version\IPv6;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for network connection entity
 */
final class ConnectionDetailEthernetTest extends TestCase {

	/**
	 * @var string NetworkManager data directory
	 */
	private const NM_DATA = TESTER_DIR . '/data/networkManager/';

	/**
	 * @var string Network interface name
	 */
	private const INTERFACE = 'eth0';

	/**
	 * @var string Network connection name
	 */
	private const NAME = 'eth0';

	/**
	 * @var string Connection UUID
	 */
	private const UUID = '25ab1b06-2a86-40a9-950f-1c576ddcd35a';

	/**
	 * @var UuidInterface Network connection UUID
	 */
	private readonly UuidInterface $uuid;

	/**
	 * @var ConnectionTypes Network connection type
	 */
	private readonly ConnectionTypes $type;

	/**
	 * @var IPv4Connection IPv4 network connection entity
	 */
	private IPv4Connection $ipv4;

	/**
	 * @var IPv6Connection IPv6 network connection entity
	 */
	private IPv6Connection $ipv6;

	/**
	 * @var ConnectionDetail Network connection entity
	 */
	private ConnectionDetail $entity;

	/**
	 * Sets up the test environment
	 */
	public function __construct() {
		$this->uuid = Uuid::fromString(self::UUID);
		$this->type = ConnectionTypes::ETHERNET;
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$autoConnect = new AutoConnect(true, 0, -1);
		$this->createIpv4Connection();
		$this->createIpv6Connection();
		$this->entity = new ConnectionDetail(self::NAME, $this->uuid, $this->type, self::INTERFACE, $autoConnect, $this->ipv4, $this->ipv6);
	}

	/**
	 * Creates the IPv4 network connection entity
	 */
	private function createIpv4Connection(): void {
		$method = IPv4Methods::MANUAL;
		$addresses = [new IPv4Address(IPv4::factory('192.168.1.2'), 24)];
		$gateway = IPv4::factory('192.168.1.1');
		$dns = [IPv4::factory('192.168.1.1')];
		$this->ipv4 = new IPv4Connection($method, $addresses, $gateway, $dns, null);
	}

	/**
	 * Creates the IPv6 network connection entity
	 */
	private function createIpv6Connection(): void {
		$method = IPv6Methods::MANUAL;
		$addresses = [
			new IPv6Address(IPv6::factory('2001:470:5bb2::2'), 64),
		];
		$gateway = IPv6::factory('fe80::1');
		$dns = [IPv6::factory('2001:470:5bb2::1')];
		$this->ipv6 = new IPv6Connection($method, $addresses, $gateway, $dns, null);
	}

	/**
	 * Tests the function to deserialize connection configuration from JSON
	 */
	public function testJsonDeserialize(): void {
		$json = Json::decode(FileSystem::read(self::NM_DATA . 'fromForm/' . self::UUID . '.json'));
		$json->uuid = self::UUID;
		$json->type = $this->type->value;
		$json->interface = self::INTERFACE;
		$actual = ConnectionDetail::jsonDeserialize($json);
		$autoConnect = new AutoConnect(true, 1, 10);
		$ipv4Addresses = [IPv4Address::fromPrefix('10.0.0.2/16')];
		$ipv4Gateway = IPv4::factory('10.0.0.1');
		$ipv4Dns = [IPv4::factory('10.0.0.1'), IPv4::factory('1.1.1.1')];
		$ipv4 = new IPv4Connection(IPv4Methods::MANUAL, $ipv4Addresses, $ipv4Gateway, $ipv4Dns, null);
		$ipv6Addresses = [IPv6Address::fromPrefix('2001:470:5bb2:2::2/64')];
		$ipv6Gateway = IPv6::factory('fe80::1');
		$ipv6Dns = [IPv6::factory('2001:470:5bb2:2::1')];
		$ipv6 = new IPv6Connection(IPv6Methods::MANUAL, $ipv6Addresses, $ipv6Gateway, $ipv6Dns, null);
		$expected = new ConnectionDetail(self::NAME, $this->uuid, $this->type, self::INTERFACE, $autoConnect, $ipv4, $ipv6);
		Assert::equal($expected, $actual);
	}

	/**
	 * Tests the function to deserialize network connection entity from nmcli connection configuration
	 */
	public function testNmCliDeserialize(): void {
		$nmCli = FileSystem::read(self::NM_DATA . self::UUID . '.conf');
		$nmCli = NmCliConnection::decode($nmCli);
		Assert::equal($this->entity, ConnectionDetail::nmCliDeserialize($nmCli));
	}

	/**
	 * Tests the function to get the network connection UUID
	 */
	public function testGetUuid(): void {
		Assert::same($this->uuid, $this->entity->getUuid());
	}

	/**
	 * Tests the function to get the network connection type
	 */
	public function testGetType(): void {
		Assert::same($this->type, $this->entity->getType());
	}

	/**
	 * Tests the function to get the network interface name
	 */
	public function testGetInterfaceName(): void {
		Assert::same(self::INTERFACE, $this->entity->getInterfaceName());
	}

	/**
	 * Tests the function to serialize network connection entity into JSON
	 */
	public function testJsonSerialize(): void {
		$json = FileSystem::read(self::NM_DATA . 'toForm/' . self::UUID . '.json');
		$expected = Json::decode($json, forceArrays: true);
		Assert::same($expected, $this->entity->jsonSerialize());
	}

}

$test = new ConnectionDetailEthernetTest();
$test->run();
