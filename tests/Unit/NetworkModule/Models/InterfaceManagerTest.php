<?php

/**
 * TEST: App\NetworkModule\Models\InterfaceManager
 * @covers App\NetworkModule\Models\InterfaceManager
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

namespace Tests\Unit\NetworkModule\Models;

use App\NetworkModule\Entities\InterfaceStatus;
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
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new InterfaceManager($this->commandManager);
	}

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
		$output = FileSystem::read(TESTER_DIR . '/data/networkManager/interfaces.txt');
		$this->receiveCommand('nmcli -t -f GENERAL device show', true, $output);
		$expected = [
			new InterfaceStatus('eth0', '02:42:A7:2C:5C:98', null, null, InterfaceTypes::ETHERNET(), InterfaceStates::CONNECTED(), Uuid::fromString('38708e8a-d842-38ae-9e66-3718361ac0b7')),
			new InterfaceStatus('wlan0', '12:42:A7:2C:5C:98', 'ST-Ericsson', null, InterfaceTypes::WIFI(), InterfaceStates::DISCONNECTED(), null),
			new InterfaceStatus('lo', '00:00:00:00:00:00', null, null, InterfaceTypes::LOOPBACK(), InterfaceStates::UNMANAGED(), null),
		];
		Assert::equal($expected, $this->manager->list());
	}

}

$test = new InterfaceManagerTest();
$test->run();
