<?php

/**
 * TEST: App\GatewayModule\Models\NetworkManager
 * @covers App\GatewayModule\Models\NetworkManager
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

namespace Tests\Unit\GatewayModule\Models;

use App\GatewayModule\Models\NetworkManager;
use Iqrf\CommandExecutor\Tester\Traits\CommandExecutorTestCase;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for Network manager
 */
final class NetworkManagerTest extends TestCase {

	use CommandExecutorTestCase;

	/**
	 * Executed commands
	 */
	private const COMMANDS = [
		'hostname' => 'hostname -f',
		'ipAddressesEth0' => 'ip a s \'eth0\' | grep inet | grep global | grep -v temporary | awk \'{print $2}\' | grep \'/\'',
		'ipAddressesWlan0' => 'ip a s \'wlan0\' | grep inet | grep global | grep -v temporary | awk \'{print $2}\' | grep \'/\'',
		'macAddresses' => 'cat /sys/class/net/eth0/address',
		'networkAdapters' => 'ls /sys/class/net | awk \'{ print $0 }\'',
	];

	/**
	 * @var NetworkManager Network manager with mocked command manager
	 */
	private NetworkManager $manager;

	/**
	 * Tests the function to get hostname of the gateway
	 */
	public function testGetHostname(): void {
		$expected = 'gateway';
		$this->receiveCommand(
			command: self::COMMANDS['hostname'],
			stdout: $expected,
		);
		Assert::same($expected, $this->manager->getHostname());
	}

	/**
	 * Tests the function to get hostname of the gateway (POSIX hostname)
	 */
	public function testGetHostnamePosix(): void {
		$this->receiveCommand(
			command: self::COMMANDS['hostname'],
			stderr: 'ERROR',
			exitCode: 1,
		);
		Assert::same(gethostname(), $this->manager->getHostname());
	}

	/**
	 * Tests the function to get information about network interfaces
	 */
	public function testGetInterfaces(): void {
		$this->receiveCommand(
			command: self::COMMANDS['networkAdapters'],
			needSudo: true,
			stdout: 'eth0' . PHP_EOL . 'lo',
		);
		$this->receiveCommand(
			command: self::COMMANDS['ipAddressesEth0'],
			needSudo: true,
			stdout: '192.168.1.100/24' . PHP_EOL . 'fda9:d95:d5b1::64/64',
		);
		$this->receiveCommand(
			command: self::COMMANDS['macAddresses'],
			needSudo: true,
			stdout: '01:02:03:04:05:06',
		);
		$expected = [
			[
				'name' => 'eth0',
				'macAddress' => '01:02:03:04:05:06',
				'ipAddresses' => ['192.168.1.100', 'fda9:d95:d5b1::64'],
			],
		];
		Assert::same($expected, $this->manager->getInterfaces());
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->setUpCommandExecutor();
		$this->manager = new NetworkManager($this->commandExecutor);
	}

}

$test = new NetworkManagerTest();
$test->run();
