<?php

/**
 * TEST: App\GatewayModule\Models\InfoManager
 * @covers App\GatewayModule\Models\InfoManager
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

namespace Tests\Unit\GatewayModule\Models;

use App\GatewayModule\Models\InfoManager;
use App\GatewayModule\Models\NetworkManager;
use App\GatewayModule\Models\VersionManager;
use Mockery;
use Mockery\MockInterface;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for Gateway info manager
 */
final class InfoManagerTest extends CommandTestCase {

	/**
	 * @var MockInterface|NetworkManager Mocked network manager
	 */
	private $networkManager;

	/**
	 * @var InfoManager Gateway Info manager with mocked command manager
	 */
	private InfoManager $manager;

	/**
	 * @var MockInterface|VersionManager Mocked version manager
	 */
	private $versionManager;

	/**
	 * @var array<string, string> Executed commands
	 */
	private const COMMANDS = [
		'deviceTreeName' => 'cat /proc/device-tree/model',
		'dmiBoardName' => 'cat /sys/class/dmi/id/board_name',
		'dmiBoardVendor' => 'cat /sys/class/dmi/id/board_vendor',
		'dmiBoardVersion' => 'cat /sys/class/dmi/id/board_version',
		'gw' => 'cat /etc/iqrf-gateway.json',
		'gitBranches' => 'git branch -v --no-abbrev',
		'osInfo' => 'cat /etc/os-release',
		'uptime' => 'uptime -p',
	];

	/**
	 * Expected outputs
	 */
	private const EXPECTED = [
		'board' => 'MICRORISC s.r.o. IQD-GW-01',
		'gwId' => '0242fc1e6f85b296',
		'gwImage' => 'gw v1.0.0',
		'os' => [
			'name' => 'Armbian 21.11.0-trunk Buster',
			'homePage' => 'https://www.debian.org/',
		],
		'versions' => [
			'cloudProvisioning' => 'v1.0.0',
			'controller' => 'v1.0.0',
			'daemon' => 'v2.3.0',
			'menderClient' => '3.5.2',
			'menderConnect' => 'v2.2.0',
			'setter' => 'v1.0.0',
			'uploader' => 'v1.0.0',
			'webapp' => 'v2.0.0',
		],
		'hostname' => 'gateway',
		'interfaces' => [
			[
				'name' => 'eth0',
				'macAddress' => '01:02:03:04:05:06',
				'ipAddresses' => ['192.168.1.100', 'fda9:d95:d5b1::64'],
			],
		],
		'diskUsages' => [
			[
				'fsName' => '/dev/sda1',
				'fsType' => 'ext4',
				'size' => '227 GB',
				'used' => '191.58 GB',
				'available' => '23.87 GB',
				'usage' => '84.4%',
				'mount' => '/',
			],
		],
		'memoryUsage' => [
			'size' => '7.66 GB',
			'used' => '6.25 GB',
			'free' => '244.56 MB',
			'shared' => '344.99 MB',
			'buffers' => '110.46 MB',
			'cache' => '1.06 GB',
			'available' => '838.5 MB',
			'usage' => '81.6%',
		],
		'swapUsage' => [
			'size' => '7.72 GB',
			'used' => '2.1 GB',
			'free' => '5.63 GB',
			'usage' => '27.15%',
		],
		'uptime' => 'up 2 hours, 30 minutes',
	];

	/**
	 * Tests the function to get information about the board (via IQRF GW json)
	 */
	public function testGetBoardGw(): void {
		$output = '{"gwProduct":"IQD-GW-01","gwManufacturer":"MICRORISC s.r.o."}';
		$this->receiveCommand(self::COMMANDS['gw'], true, $output);
		Assert::same(self::EXPECTED['board'], $this->manager->getBoard());
	}

	/**
	 * Tests the function to get information about the board (via device tree)
	 */
	public function testGetBoardDeviceTree(): void {
		$expected = 'Raspberry Pi 2 Models B Rev 1.1';
		$this->receiveCommand(self::COMMANDS['gw'], true);
		$this->receiveCommand(self::COMMANDS['deviceTreeName'], true, $expected);
		Assert::same($expected, $this->manager->getBoard());
	}

	/**
	 * Tests the function to get information about the board (via DMI)
	 */
	public function testGetBoardDmi(): void {
		$this->receiveCommand(self::COMMANDS['gw'], true);
		$this->receiveCommand(self::COMMANDS['deviceTreeName'], true);
		$this->receiveCommand(self::COMMANDS['dmiBoardVendor'], true, 'AAEON');
		$this->receiveCommand(self::COMMANDS['dmiBoardName'], true, 'UP-APL01');
		$this->receiveCommand(self::COMMANDS['dmiBoardVersion'], true, 'V0.4');
		Assert::same('AAEON UP-APL01 (V0.4)', $this->manager->getBoard());
	}

	/**
	 * Tests the function to get information about the board (unknown method)
	 */
	public function testGetBoardUnknown(): void {
		$this->receiveCommand(self::COMMANDS['gw'], true);
		$this->receiveCommand(self::COMMANDS['deviceTreeName'], true);
		$this->receiveCommand(self::COMMANDS['dmiBoardVendor'], true);
		$this->receiveCommand(self::COMMANDS['dmiBoardName'], true);
		$this->receiveCommand(self::COMMANDS['dmiBoardVersion'], true);
		Assert::same('UNKNOWN', $this->manager->getBoard());
	}

	/**
	 * Tests the function to get the gateway ID
	 */
	public function testGetId(): void {
		$output = '{"gwId":"0242fc1e6f85b296"}';
		$this->receiveCommand(self::COMMANDS['gw'], true, $output);
		Assert::same(self::EXPECTED['gwId'], $this->manager->getId());
	}

	/**
	 * Tests the function to get the gateway ID (invalid JSON)
	 */
	public function testGetIdInvalidJson(): void {
		$output = '{"gwId":"0242fc1e6f85b296",}';
		$this->receiveCommand(self::COMMANDS['gw'], true, $output);
		Assert::null($this->manager->getId());
	}

	/**
	 * Tests the function to get the gateway image version
	 */
	public function testGetImage(): void {
		$output = '{"gwImage": "gw v1.0.0"}';
		$this->receiveCommand(self::COMMANDS['gw'], true, $output);
		Assert::same(self::EXPECTED['gwImage'], $this->manager->getImage());
	}

	/**
	 * Tests the function to get the gateway image version (invalid JSON)
	 */
	public function testGetImageInvalidJson(): void {
		$output = '{"gwImage": "gw v1.0.0",}';
		$this->receiveCommand(self::COMMANDS['gw'], true, $output);
		Assert::null($this->manager->getImage());
	}

	/**
	 * Tests the function to get information about the operating system
	 */
	public function testGetOs(): void {
		$this->receiveCommand(self::COMMANDS['osInfo'], null, FileSystem::read(TESTER_DIR . '/data/os-release'));
		Assert::same(self::EXPECTED['os'], $this->manager->getOs());
	}

	/**
	 * Tests the function to read the gateway file (missing file)
	 */
	public function testReadGatewayFileMissingFle(): void {
		$stderr = 'cat: /etc/iqrf-gateway.json: No such file or directory';
		$this->receiveCommand(self::COMMANDS['gw'], true, '', $stderr, 1);
		Assert::null($this->manager->readGatewayFile());
	}

	/**
	 * Tests the function to get the gateway ID (missing property)
	 */
	public function testGetIdMissingProperty(): void {
		$output = '{"id":"0242fc1e6f85b296"}';
		$this->receiveCommand(self::COMMANDS['gw'], true, $output);
		Assert::null($this->manager->getId());
	}

	/**
	 * Tests the function to get the gateway image (missing property)
	 */
	public function testGetImageMissingProperty(): void {
		$output = '{"image": "gw v1.0.0"}';
		$this->receiveCommand(self::COMMANDS['gw'], true, $output);
		Assert::null($this->manager->getImage());
	}

	/**
	 * Tests the function to get disk usages
	 */
	public function testGetDiskUsages(): void {
		$command = 'df -l -B1 -x tmpfs -x devtmpfs -x overlay -x squashfs -T -P | awk \'{if (NR!=1) {$6="";print}}\'';
		$output = '/dev/sda1 ext4 243735838720 205705183232 25625583616  /';
		$this->receiveCommand($command, null, $output);
		Assert::same(self::EXPECTED['diskUsages'], $this->manager->getDiskUsages());
	}

	/**
	 * Tests the function to get memory usage
	 */
	public function testGetMemoryUsage(): void {
		$command = 'free -bw | awk \'{{if (NR==2) print $2,$3,$4,$5,$6,$7,$8}}\'';
		$output = '8220397568 6708125696 256442368 361750528 115830784 1139998720 879230976';
		$this->receiveCommand($command, null, $output);
		Assert::same(self::EXPECTED['memoryUsage'], $this->manager->getMemoryUsage());
	}

	/**
	 * Tests the function to get swap usage
	 */
	public function testGetSwapUsage(): void {
		$command = 'free -b | awk \'{{if (NR==3) print $2,$3,$4}}\'';
		$output = '8291086336 2250952704 6040133632';
		$this->receiveCommand($command, null, $output);
		Assert::same(self::EXPECTED['swapUsage'], $this->manager->getSwapUsage());
	}


	/**
	 * Tests the function to get swap usage (computer hasn't got swap)
	 */
	public function testGetSwapUsageWithoutSwap(): void {
		$command = 'free -b | awk \'{{if (NR==3) print $2,$3,$4}}\'';
		$output = '0 0 0 ';
		$this->receiveCommand($command, null, $output);
		Assert::null($this->manager->getSwapUsage());
	}

	/**
	 * Tests the function to convert size in bytes to human readable format
	 */
	public function testConvertSizes(): void {
		Assert::same('1000 B', $this->manager->convertSizes(1e3));
		Assert::same('953.67 MB', $this->manager->convertSizes(1e9));
		Assert::same('953.6743164063 MB', $this->manager->convertSizes(1e9, 10));
	}

	/**
	 * Tests the function to get gateway uptime
	 */
	public function testGetUptime(): void {
		$output = 'up 2 hours, 30 minutes';
		$this->receiveCommand(self::COMMANDS['uptime'], null, $output);
		Assert::same(self::EXPECTED['uptime'], $this->manager->getUptime());
	}

	/**
	 * Tests the function to return information about the gateway
	 */
	public function testGet(): void {
		$verbose = false;
		$manager = Mockery::mock(InfoManager::class, [$this->commandManager, $this->networkManager, $this->versionManager])->makePartial();
		$manager->shouldReceive('getBoard')
			->andReturn(self::EXPECTED['board']);
		$manager->shouldReceive('getId')
			->andReturn(self::EXPECTED['gwId']);
		$manager->shouldReceive('getImage')
			->andReturn(self::EXPECTED['gwImage']);
		$manager->shouldReceive('getOs')
			->andReturn(self::EXPECTED['os']);
		$this->versionManager->shouldReceive('getCloudProvisioning')
			->andReturn(self::EXPECTED['versions']['cloudProvisioning']);
		$this->versionManager->shouldReceive('getController')
			->andReturn(self::EXPECTED['versions']['controller']);
		$this->versionManager->shouldReceive('getDaemon')
			->withArgs([$verbose])
			->andReturn(self::EXPECTED['versions']['daemon']);
		$this->versionManager->shouldReceive('getMenderClient')
			->andReturn(self::EXPECTED['versions']['menderClient']);
		$this->versionManager->shouldReceive('getMenderConnect')
			->andReturn(self::EXPECTED['versions']['menderConnect']);
		$this->versionManager->shouldReceive('getSetter')
			->andReturn(self::EXPECTED['versions']['setter']);
		$this->versionManager->shouldReceive('getUploader')
			->andReturn(self::EXPECTED['versions']['uploader']);
		$this->versionManager->shouldReceive('getWebapp')
			->withArgs([$verbose])
			->andReturn(self::EXPECTED['versions']['webapp']);
		$this->networkManager->shouldReceive('getHostname')
			->andReturn(self::EXPECTED['hostname']);
		$this->networkManager->shouldReceive('getInterfaces')
			->andReturn(self::EXPECTED['interfaces']);
		$manager->shouldReceive('getDiskUsages')
			->andReturn(self::EXPECTED['diskUsages']);
		$manager->shouldReceive('getMemoryUsage')
			->andReturn(self::EXPECTED['memoryUsage']);
		$manager->shouldReceive('getSwapUsage')
			->andReturn(self::EXPECTED['swapUsage']);
		$manager->shouldReceive('getUptime')
			->andReturn(self::EXPECTED['uptime']);
		Assert::same(self::EXPECTED, $manager->get($verbose));
	}

	/**
	 * Tests the function to return brief information about the gateway
	 */
	public function testGetBrief(): void {
		$manager = Mockery::mock(InfoManager::class, [$this->commandManager, $this->networkManager, $this->versionManager])->makePartial();
		$manager->shouldReceive('getBoard')
			->andReturn(self::EXPECTED['board']);
		$expected = [
			'board' => self::EXPECTED['board'],
		];
		Assert::same($expected, $manager->getBrief());
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->networkManager = Mockery::mock(NetworkManager::class);
		$this->versionManager = Mockery::mock(VersionManager::class);
		$this->manager = new InfoManager($this->commandManager, $this->networkManager, $this->versionManager);
	}

}

$test = new InfoManagerTest();
$test->run();
