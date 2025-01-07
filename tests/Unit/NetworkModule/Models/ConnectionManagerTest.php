<?php

/**
 * TEST: App\NetworkModule\Models\ConnectionManager
 * @covers App\NetworkModule\Models\ConnectionManager
 * @phpVersion >= 7.4
 * @testCase
 */
/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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

namespace Tests\Unit\NetworkModule\Models;

use App\NetworkModule\Entities\AutoConnect;
use App\NetworkModule\Entities\Connection;
use App\NetworkModule\Entities\ConnectionDetail;
use App\NetworkModule\Entities\IPv4Address;
use App\NetworkModule\Entities\IPv4Connection;
use App\NetworkModule\Entities\IPv6Address;
use App\NetworkModule\Entities\IPv6Connection;
use App\NetworkModule\Enums\ConnectionStates;
use App\NetworkModule\Enums\ConnectionTypes;
use App\NetworkModule\Enums\IPv4Methods;
use App\NetworkModule\Enums\IPv6Methods;
use App\NetworkModule\Exceptions\NonexistentConnectionException;
use App\NetworkModule\Models\ConnectionManager;
use Darsyn\IP\Version\IPv4;
use Darsyn\IP\Version\IPv6;
use Iqrf\CommandExecutor\Tester\Traits\CommandExecutorTestCase;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Ramsey\Uuid\Uuid;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for network connection manager
 */
final class ConnectionManagerTest extends TestCase {

	use CommandExecutorTestCase;

	/**
	 * Executed commands
	 */
	private const COMMANDS = [
		'delete' => 'nmcli -t connection delete ',
		'down' => 'nmcli -t connection down ',
		'get' => 'nmcli -t -s connection show ',
		'list' => 'nmcli -t -f NAME,UUID,TYPE,DEVICE,ACTIVE,STATE connection show',
		'up' => 'nmcli -t connection up ',
	];

	/**
	 * NetworkManager data directory
	 */
	private const NM_DATA = TESTER_DIR . '/data/networkManager/';

	/**
	 * Connection UUID
	 */
	private const UUID = '25ab1b06-2a86-40a9-950f-1c576ddcd35a';

	/**
	 * @var ConnectionManager Network connection manager
	 */
	private ConnectionManager $manager;

	/**
	 * Tests the function to delete network connection
	 */
	public function testDelete(): void {
		$this->receiveCommand(
			command: self::COMMANDS['delete'] . self::UUID,
			needSudo: true,
		);
		Assert::noError(function (): void {
			$this->manager->delete(Uuid::fromString(self::UUID));
		});
	}

	/**
	 * Tests the function to delete network connection (nonexistent connection)
	 */
	public function testDeleteNonexistent(): void {
		$stderr = 'Error: unknown connection \'' . self::UUID . '\'.' .
			PHP_EOL . 'Error: cannot delete unknown connection(s): \'' . self::UUID . '\'.';
		$this->receiveCommand(
			command: self::COMMANDS['delete'] . self::UUID,
			needSudo: true,
			stderr: $stderr,
			exitCode: 10,
		);
		Assert::throws(function (): void {
			$this->manager->delete(Uuid::fromString(self::UUID));
		}, NonexistentConnectionException::class, $stderr);
	}

	/**
	 * Tests the function to deactivate network connection
	 */
	public function testDown(): void {
		$this->receiveCommand(
			command: self::COMMANDS['down'] . self::UUID,
			needSudo: true,
		);
		Assert::noError(function (): void {
			$this->manager->down(Uuid::fromString(self::UUID));
		});
	}

	/**
	 * Tests the function to deactivate network connection (nonexistent connection)
	 */
	public function testDownNonexistent(): void {
		$command = self::COMMANDS['down'] . self::UUID;
		$stderr = 'Error: \'' . self::UUID . '\' is not an active connection.' .
			PHP_EOL . 'Error: no active connection provided.';
		$this->receiveCommand(
			command: $command,
			needSudo: true,
			stderr: $stderr,
			exitCode: 10,
		);
		Assert::throws(function (): void {
			$this->manager->down(Uuid::fromString(self::UUID));
		}, NonexistentConnectionException::class, $stderr);
	}

	/**
	 * Tests the function to get detailed network connection entity
	 */
	public function testGet(): void {
		$expected = $this->createDetailedConnection();
		$command = self::COMMANDS['get'] . self::UUID;
		$this->receiveCommand(
			command: $command,
			needSudo: true,
			stdout: FileSystem::read(self::NM_DATA . self::UUID . '.conf'),
		);
		Assert::equal($expected, $this->manager->get(Uuid::fromString(self::UUID)));
	}

	/**
	 * Tests the function to get detailed network connection entity (nonexistent connection)
	 */
	public function testGetNonexistent(): void {
		$stderr = 'Error: ' . self::UUID . ' - no such connection profile.';
		$this->receiveCommand(
			command: self::COMMANDS['get'] . self::UUID,
			needSudo: true,
			stderr: $stderr,
			exitCode: 10,
		);
		Assert::throws(function (): void {
			$this->manager->get(Uuid::fromString(self::UUID));
		}, NonexistentConnectionException::class, $stderr);
	}

	/**
	 * Tests the function to list network connections
	 */
	public function testList(): void {
		$this->receiveCommand(
			command: self::COMMANDS['list'],
			needSudo: true,
			stdout: <<<'EOT'
eth0:25ab1b06-2a86-40a9-950f-1c576ddcd35a:802-3-ethernet:eth0:yes:activated
wlan0:dd1c59ea-6f5d-471c-8fe7-e066761b9764:802-11-wireless::no:
EOT,
		);
		$expected = [
			new Connection('eth0', Uuid::fromString('25ab1b06-2a86-40a9-950f-1c576ddcd35a'), ConnectionTypes::ETHERNET, 'eth0', true, ConnectionStates::ACTIVATED),
			new Connection('wlan0', Uuid::fromString('dd1c59ea-6f5d-471c-8fe7-e066761b9764'), ConnectionTypes::WIFI, '', false, ConnectionStates::DEACTIVATED),
		];
		Assert::equal($expected, $this->manager->list());
	}

	/**
	 * Tests the function to list network connections (empty list)
	 */
	public function testListEmpty(): void {
		$this->receiveCommand(command: self::COMMANDS['list'], needSudo: true);
		$expected = [];
		Assert::equal($expected, $this->manager->list());
	}

	/**
	 * Tests the function to edit the network connection
	 */
	public function testEdit(): void {
		$this->receiveCommand(
			command: 'nmcli -t -s connection show ' . self::UUID,
			needSudo: true,
			stdout: FileSystem::read(self::NM_DATA . self::UUID . '.conf'),
		);
		$json = FileSystem::read(self::NM_DATA . 'fromForm/' . self::UUID . '.json');
		$jsonData = Json::decode($json);
		$this->receiveCommand(
			command: 'nmcli -t connection modify 25ab1b06-2a86-40a9-950f-1c576ddcd35a connection.id "eth0" connection.interface-name "eth0" connection.autoconnect "yes" connection.autoconnect-priority "1" connection.autoconnect-retries "10" ipv4.method "manual" ipv4.addresses "10.0.0.2/16" ipv4.gateway "10.0.0.1" ipv4.dns "10.0.0.1 1.1.1.1" ipv6.method "manual" ipv6.addresses "2001:470:5bb2:2::2/64" ipv6.gateway "fe80::1" ipv6.dns "2001:470:5bb2:2::1" ',
			needSudo: true,
		);
		Assert::noError(function () use ($jsonData): void {
			$uuid = Uuid::fromString(self::UUID);
			$this->manager->edit($uuid, $jsonData);
		});
	}

	/**
	 * Tests the function to activate a connection on the interface
	 */
	public function testUp(): void {
		$connection = $this->createDetailedConnection();
		$this->receiveCommand(
			command: self::COMMANDS['up'] . self::UUID,
			needSudo: true,
		);
		Assert::noError(function () use ($connection): void {
			$this->manager->up($connection->getUuid());
		});
	}

	/**
	 * Tests the function to activate a connection on the specific interface
	 */
	public function testUpInterface(): void {
		$connection = $this->createDetailedConnection();
		$this->receiveCommand(
			command: self::COMMANDS['up'] . self::UUID . ' ifname eth0',
			needSudo: true,
		);
		Assert::noError(function () use ($connection): void {
			$this->manager->up($connection->getUuid(), 'eth0');
		});
	}

	/**
	 * Tests the function to activate a connection on the interface (nonexistent connection)
	 */
	public function testUpNonexistent(): void {
		$connection = $this->createDetailedConnection();
		$stderr = 'Error: unknown connection \'' . self::UUID . '\'.';
		$this->receiveCommand(
			command: self::COMMANDS['up'] . self::UUID,
			needSudo: true,
			stderr: $stderr,
			exitCode: 10,
		);
		Assert::throws(function () use ($connection): void {
			$this->manager->up($connection->getUuid());
		}, NonexistentConnectionException::class, $stderr);
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->setUpCommandExecutor();
		$this->manager = new ConnectionManager($this->commandExecutor);
	}

	/**
	 * Creates the detailed network connection entity
	 * @return ConnectionDetail Detailed network connection entity
	 */
	private function createDetailedConnection(): ConnectionDetail {
		$uuid = Uuid::fromString(self::UUID);
		$type = ConnectionTypes::ETHERNET;
		$autoConnect = new AutoConnect(true, 0, -1);
		$ipv4 = $this->createIpv4Connection();
		$ipv6 = $this->createIpv6Connection();
		return new ConnectionDetail('eth0', $uuid, $type, 'eth0', $autoConnect, $ipv4, $ipv6);
	}

	/**
	 * Creates the IPv4 network connection entity
	 * @return IPv4Connection IPv4 network connection entity
	 */
	private function createIpv4Connection(): IPv4Connection {
		$method = IPv4Methods::MANUAL;
		$addresses = [new IPv4Address(IPv4::factory('192.168.1.2'), 24)];
		$gateway = IPv4::factory('192.168.1.1');
		$dns = [IPv4::factory('192.168.1.1')];
		return new IPv4Connection($method, $addresses, $gateway, $dns, null);
	}

	/**
	 * Creates the IPv6 network connection entity
	 * @return IPv6Connection IPv6 network connection entity
	 */
	private function createIpv6Connection(): IPv6Connection {
		$method = IPv6Methods::MANUAL;
		$addresses = [new IPv6Address(IPv6::factory('2001:470:5bb2::2'), 64)];
		$gateway = IPv6::factory('fe80::1');
		$dns = [IPv6::factory('2001:470:5bb2::1')];
		return new IPv6Connection($method, $addresses, $gateway, $dns, null);
	}

}

$test = new ConnectionManagerTest();
$test->run();
