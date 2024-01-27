<?php

/**
 * TEST: App\NetworkModule\Models\InterfaceManager
 * @covers App\NetworkModule\Models\InterfaceManager
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

namespace Tests\Unit\NetworkModule\Models;

use App\NetworkModule\Entities\AvailableConnection;
use App\NetworkModule\Entities\InterfaceStatus;
use App\NetworkModule\Enums\ConnectivityState;
use App\NetworkModule\Enums\InterfaceStates;
use App\NetworkModule\Enums\InterfaceTypes;
use App\NetworkModule\Exceptions\NonexistentDeviceException;
use App\NetworkModule\Models\InterfaceManager;
use Nette\Utils\FileSystem;
use Ramsey\Uuid\Uuid;
use Tester\Assert;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for network interface manager
 */
final class InterfaceManagerTest extends CommandTestCase {

	/**
	 * @var InterfaceManager Network interface manager
	 */
	private InterfaceManager $manager;

	/**
	 * Tests the function to connect the network interface
	 */
	public function testConnect(): void {
		$this->receiveCommand('nmcli -t device connect \'eth0\'', true);
		Assert::noError(function (): void {
			$this->manager->connect('eth0');
		});
	}

	/**
	 * Tests the function to connect the network interface if it does not exist
	 */
	public function testConnectNonexistent(): void {
		$command = 'nmcli -t device connect \'testDev\'';
		$stderr = 'Error: Device \'testDev\' not found.';
		$this->receiveCommand($command, true, '', $stderr, 10);
		Assert::throws(function (): void {
			$this->manager->connect('testDev');
		}, NonexistentDeviceException::class, $stderr);
	}

	/**
	 * Tests the function to disconnect the network interface
	 */
	public function testDisconnect(): void {
		$this->receiveCommand('nmcli -t device disconnect \'eth0\'', true);
		Assert::noError(function (): void {
			$this->manager->disconnect('eth0');
		});
	}

	/**
	 * Tests the function to connect the network interface if it does not exist
	 */
	public function testDisconnectNonexistent(): void {
		$command = 'nmcli -t device disconnect \'testDev\'';
		$stderr = 'Error: Device \'testDev\' not found.';
		$this->receiveCommand($command, true, '', $stderr, 10);
		Assert::throws(function (): void {
			$this->manager->disconnect('testDev');
		}, NonexistentDeviceException::class, $stderr);
	}

	/**
	 * Tests the function to list network interfaces
	 */
	public function testList(): void {
		$this->receiveCommand('nmcli -t -f DEVICE,TYPE device status', true, FileSystem::read(TESTER_DIR . '/data/networkManager/interfaces.txt'));
		foreach (['eth0', 'wlan0', 'lo'] as $interface) {
			$this->receiveCommand('nmcli -t -f GENERAL,CONNECTIONS.AVAILABLE-CONNECTIONS device show ' . $interface, true, FileSystem::read(TESTER_DIR . '/data/networkManager/interfaces/' . $interface . '.conf'));
		}
		$expected = [
			new InterfaceStatus(
				name: 'eth0',
				macAddress: 'E4:5F:01:53:0A:E3',
				manufacturer: null,
				model: null,
				type: InterfaceTypes::ETHERNET,
				state: InterfaceStates::CONNECTED,
				connection: Uuid::fromString('81fb4b68-ca29-3f53-914f-6368428c9a4b'),
				ipv4Connectivity: ConnectivityState::FULL,
				ipv6Connectivity: ConnectivityState::FULL,
				availableConnections: [
					new AvailableConnection(
						name: 'Wired connection 1',
						uuid: Uuid::fromString('81fb4b68-ca29-3f53-914f-6368428c9a4b'),
					),
				],
			),
			new InterfaceStatus(
				name: 'wlan0',
				macAddress: 'E4:5F:01:53:0A:E6',
				manufacturer: 'Broadcom Corp.',
				model: 'BCM43438 combo WLAN and Bluetooth Low Energy (BLE)',
				type: InterfaceTypes::WIFI,
				state: InterfaceStates::DISCONNECTED,
				connection: null,
				ipv4Connectivity: ConnectivityState::NONE,
				ipv6Connectivity: ConnectivityState::NONE,
				availableConnections: [],
			),
			new InterfaceStatus(
				name: 'lo',
				macAddress: '00:00:00:00:00:00',
				manufacturer: null,
				model: null,
				type: InterfaceTypes::LOOPBACK,
				state: InterfaceStates::UNMANAGED,
				connection: null,
				ipv4Connectivity: ConnectivityState::UNKNOWN,
				ipv6Connectivity: ConnectivityState::UNKNOWN,
			),
		];
		Assert::equal($expected, $this->manager->list());
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new InterfaceManager($this->commandManager);
	}

}

$test = new InterfaceManagerTest();
$test->run();
