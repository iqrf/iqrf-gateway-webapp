<?php

/**
 * TEST: App\NetworkModule\Entities\InterfaceStatus
 * @covers App\NetworkModule\Entities\InterfaceStatus
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

use App\NetworkModule\Entities\AvailableConnection;
use App\NetworkModule\Entities\InterfaceStatus;
use App\NetworkModule\Enums\ConnectivityState;
use App\NetworkModule\Enums\InterfaceStates;
use App\NetworkModule\Enums\InterfaceTypes;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for network interface entity
 */
final class InterfaceStatusTest extends TestCase {

	/**
	 * Network connection UUID
	 */
	private const CONNECTION = 'f61b25c9-66d7-400e-add0-d2a30c57b65c';

	/**
	 * Network interface name
	 */
	private const NAME = 'eth0';

	/**
	 * Network interface MAC address
	 */
	private const MAC_ADDRESS = '00:00:00:00:00:00';

	/**
	 * Network interface manufacturer
	 */
	private const MANUFACTURER = 'Manufacturer';

	/**
	 * Network interface model
	 */
	private const MODEL = 'Model';

	/**
	 * Network interface type
	 */
	private const TYPE = InterfaceTypes::ETHERNET;

	/**
	 * Network interface state
	 */
	private const STATE = InterfaceStates::CONNECTED;

	/**
	 * IPv4 connectivity state
	 */
	private const IPv4_CONNECTIVITY = ConnectivityState::FULL;

	/**
	 * IPv6 connectivity state
	 */
	private const IPv6_CONNECTIVITY = ConnectivityState::NONE;

	/**
	 * @var array<AvailableConnection> Available connections
	 */
	private readonly array $availableConnections;

	/**
	 * @var InterfaceStatus Network interface entity
	 */
	private readonly InterfaceStatus $entity;

	/**
	 * @var UuidInterface Network connection UUID
	 */
	private readonly UuidInterface $connection;

	/**
	 * Sets up the test environment
	 */
	public function __construct() {
		$this->connection = Uuid::fromString(self::CONNECTION);
		$this->availableConnections = [
			new AvailableConnection('eth0', Uuid::fromString('25ab1b06-2a86-40a9-950f-1c576ddcd35a')),
		];
		$this->entity = new InterfaceStatus(
			name: self::NAME,
			macAddress: self::MAC_ADDRESS,
			manufacturer: self::MANUFACTURER,
			model: self::MODEL,
			type: self::TYPE,
			state: self::STATE,
			connection: $this->connection,
			ipv4Connectivity: self::IPv4_CONNECTIVITY,
			ipv6Connectivity: self::IPv6_CONNECTIVITY,
			availableConnections: $this->availableConnections,
		);
	}

	/**
	 * Tests the function to deserialize network interface entity from a nmcli row
	 */
	public function testNmCliDeserialize(): void {
		$string = 'GENERAL.DEVICE:eth0
GENERAL.TYPE:ethernet
GENERAL.VENDOR:Manufacturer
GENERAL.PRODUCT:Model
GENERAL.HWADDR:00:00:00:00:00:00
GENERAL.MTU:1500
GENERAL.STATE:100 (connected)
GENERAL.CON-UUID:f61b25c9-66d7-400e-add0-d2a30c57b65c
GENERAL.IP4-CONNECTIVITY:4 (full)
GENERAL.IP6-CONNECTIVITY:0 (none)
CONNECTIONS.AVAILABLE-CONNECTIONS[1]:25ab1b06-2a86-40a9-950f-1c576ddcd35a | eth0
';
		Assert::equal($this->entity, InterfaceStatus::nmCliDeserialize($string));
	}

	/**
	 * Tests the function to get network interface type
	 */
	public function testGetType(): void {
		Assert::same(self::TYPE, $this->entity->getType());
	}

	/**
	 * Tests the function to serialize network interface status entity into JSON
	 */
	public function testJsonSerialize(): void {
		$expected = [
			'name' => self::NAME,
			'macAddress' => self::MAC_ADDRESS,
			'manufacturer' => self::MANUFACTURER,
			'model' => self::MODEL,
			'type' => self::TYPE->value,
			'state' => self::STATE->value,
			'connection' => self::CONNECTION,
			'availableConnections' => [
				['uuid' => '25ab1b06-2a86-40a9-950f-1c576ddcd35a', 'name' => 'eth0'],
			],
			'connectivity' => [
				'ipv4' => self::IPv4_CONNECTIVITY->value,
				'ipv6' => self::IPv6_CONNECTIVITY->value,
			],
		];
		Assert::same($expected, $this->entity->jsonSerialize());
	}

}

$test = new InterfaceStatusTest();
$test->run();
