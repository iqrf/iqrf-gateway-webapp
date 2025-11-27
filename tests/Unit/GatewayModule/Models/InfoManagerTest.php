<?php

/**
 * TEST: App\GatewayModule\Models\InfoManager
 * @covers App\GatewayModule\Models\InfoManager
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

use App\GatewayModule\Models\InfoManager;
use App\GatewayModule\Models\NetworkManager;
use App\GatewayModule\Models\VersionManager;
use Iqrf\CommandExecutor\Tester\Traits\CommandExecutorTestCase;
use Mockery;
use Mockery\MockInterface;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for Gateway info manager
 */
final class InfoManagerTest extends TestCase {

	use CommandExecutorTestCase;

	/**
	 * Executed commands
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
		'emmcHealth' => 'mmc extcsd read /dev/mmcblk0 | grep -e EXT_CSD_DEVICE_LIFE_TIME_EST_TYP_A -e EXT_CSD_DEVICE_LIFE_TIME_EST_TYP_B -e EXT_CSD_PRE_EOL_INFO',
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
			'influxdbBridge' => 'v1.0.0',
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
		'emmcHealth' => [
			'slc_region' => '90 %',
			'mlc_region' => '90 %',
			'status' => 'normal',
		],
	];

	/**
	 * @var MockInterface|NetworkManager Mocked network manager
	 */
	private MockInterface|NetworkManager $networkManager;

	/**
	 * @var InfoManager Gateway Info manager with mocked command manager
	 */
	private InfoManager $manager;

	/**
	 * @var MockInterface|VersionManager Mocked version manager
	 */
	private MockInterface|VersionManager $versionManager;

	/**
	 * Tests the function to get information about the board (via IQRF GW json)
	 */
	public function testGetBoardGw(): void {
		$this->receiveCommand(
			command: self::COMMANDS['gw'],
			needSudo: true,
			stdout: '{"gwProduct":"IQD-GW-01","gwManufacturer":"MICRORISC s.r.o."}',
		);
		Assert::same(self::EXPECTED['board'], $this->manager->getBoard());
	}

	/**
	 * Tests the function to get information about the board (via device tree)
	 */
	public function testGetBoardDeviceTree(): void {
		$expected = 'Raspberry Pi 2 Models B Rev 1.1';
		$this->receiveCommand(
			command: self::COMMANDS['gw'],
			needSudo: true,
		);
		$this->receiveCommand(
			command: self::COMMANDS['deviceTreeName'],
			needSudo: true,
			stdout: $expected,
		);
		Assert::same($expected, $this->manager->getBoard());
	}

	/**
	 * Tests the function to get information about the board (via DMI)
	 */
	public function testGetBoardDmi(): void {
		$this->receiveCommand(
			command: self::COMMANDS['gw'],
			needSudo: true,
		);
		$this->receiveCommand(
			command: self::COMMANDS['deviceTreeName'],
			needSudo: true,
		);
		$this->receiveCommand(
			command: self::COMMANDS['dmiBoardVendor'],
			needSudo: true,
			stdout: 'AAEON',
		);
		$this->receiveCommand(
			command: self::COMMANDS['dmiBoardName'],
			needSudo: true,
			stdout: 'UP-APL01',
		);
		$this->receiveCommand(
			command: self::COMMANDS['dmiBoardVersion'],
			needSudo: true,
			stdout: 'V0.4',
		);
		Assert::same('AAEON UP-APL01 (V0.4)', $this->manager->getBoard());
	}

	/**
	 * Tests the function to get information about the board (unknown method)
	 */
	public function testGetBoardUnknown(): void {
		$this->receiveCommand(
			command: self::COMMANDS['gw'],
			needSudo: true,
		);
		$this->receiveCommand(
			command: self::COMMANDS['deviceTreeName'],
			needSudo: true,
		);
		$this->receiveCommand(
			command: self::COMMANDS['dmiBoardVendor'],
			needSudo: true,
		);
		$this->receiveCommand(
			command: self::COMMANDS['dmiBoardName'],
			needSudo: true,
		);
		$this->receiveCommand(
			command: self::COMMANDS['dmiBoardVersion'],
			needSudo: true,
		);
		Assert::same('UNKNOWN', $this->manager->getBoard());
	}

	/**
	 * Tests the function to get the gateway ID
	 */
	public function testGetId(): void {
		$this->receiveCommand(
			command: self::COMMANDS['gw'],
			needSudo: true,
			stdout: '{"gwId":"0242fc1e6f85b296"}',
		);
		Assert::same(self::EXPECTED['gwId'], $this->manager->getId());
	}

	/**
	 * Tests the function to get the gateway ID (invalid JSON)
	 */
	public function testGetIdInvalidJson(): void {
		$this->receiveCommand(
			command: self::COMMANDS['gw'],
			needSudo: true,
			stdout: '{"gwId":"0242fc1e6f85b296",}',
		);
		Assert::null($this->manager->getId());
	}

	/**
	 * Tests the function to get the gateway image version
	 */
	public function testGetImage(): void {
		$this->receiveCommand(
			command: self::COMMANDS['gw'],
			needSudo: true,
			stdout: '{"gwImage": "gw v1.0.0"}',
		);
		Assert::same(self::EXPECTED['gwImage'], $this->manager->getImage());
	}

	/**
	 * Tests the function to get the gateway image version (invalid JSON)
	 */
	public function testGetImageInvalidJson(): void {
		$this->receiveCommand(
			command: self::COMMANDS['gw'],
			needSudo: true,
			stdout: '{"gwImage": "gw v1.0.0",}',
		);
		Assert::null($this->manager->getImage());
	}

	/**
	 * Tests the function to get information about the operating system
	 */
	public function testGetOs(): void {
		$this->receiveCommand(
			command: self::COMMANDS['osInfo'],
			stdout: FileSystem::read(TESTER_DIR . '/data/os-release'),
		);
		Assert::same(self::EXPECTED['os'], $this->manager->getOs());
	}

	/**
	 * Tests the function to read the gateway file (missing file)
	 */
	public function testReadGatewayFileMissingFle(): void {
		$this->receiveCommand(
			command: self::COMMANDS['gw'],
			needSudo: true,
			stderr: 'cat: /etc/iqrf-gateway.json: No such file or directory',
			exitCode: 1,
		);
		Assert::null($this->manager->readGatewayFile());
	}

	/**
	 * Tests the function to get the gateway ID (missing property)
	 */
	public function testGetIdMissingProperty(): void {
		$this->receiveCommand(
			command: self::COMMANDS['gw'],
			needSudo: true,
			stdout: '{"id":"0242fc1e6f85b296"}',
		);
		Assert::null($this->manager->getId());
	}

	/**
	 * Tests the function to get the gateway image (missing property)
	 */
	public function testGetImageMissingProperty(): void {
		$this->receiveCommand(
			command: self::COMMANDS['gw'],
			needSudo: true,
			stdout: '{"image": "gw v1.0.0"}',
		);
		Assert::null($this->manager->getImage());
	}

	/**
	 * Tests the function to get disk usages
	 */
	public function testGetDiskUsages(): void {
		$this->receiveCommand(
			command: 'df -l -B1 -x tmpfs -x devtmpfs -x overlay -x squashfs -T -P | awk \'{if (NR!=1) {$6="";print}}\'',
			stdout: '/dev/sda1 ext4 243735838720 205705183232 25625583616  /',
		);
		Assert::same(self::EXPECTED['diskUsages'], $this->manager->getDiskUsages());
	}

	/**
	 * Tests the function to get memory usage
	 */
	public function testGetMemoryUsage(): void {
		$this->receiveCommand(
			command: 'free -bw | awk \'{{if (NR==2) print $2,$3,$4,$5,$6,$7,$8}}\'',
			stdout: '8220397568 6708125696 256442368 361750528 115830784 1139998720 879230976',
		);
		Assert::same(self::EXPECTED['memoryUsage'], $this->manager->getMemoryUsage());
	}

	/**
	 * Tests the function to get swap usage
	 */
	public function testGetSwapUsage(): void {
		$this->receiveCommand(
			command: 'free -b | awk \'{{if (NR==3) print $2,$3,$4}}\'',
			stdout: '8291086336 2250952704 6040133632',
		);
		Assert::same(self::EXPECTED['swapUsage'], $this->manager->getSwapUsage());
	}

	/**
	 * Tests the function to get swap usage (computer hasn't got swap)
	 */
	public function testGetSwapUsageWithoutSwap(): void {
		$this->receiveCommand(
			command: 'free -b | awk \'{{if (NR==3) print $2,$3,$4}}\'',
			stdout: '0 0 0 ',
		);
		Assert::null($this->manager->getSwapUsage());
	}

	/**
	 * Tests the function to convert size in bytes to human-readable format
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
		$this->receiveCommand(
			command: self::COMMANDS['uptime'],
			stdout: 'up 2 hours, 30 minutes',
		);
		Assert::same(self::EXPECTED['uptime'], $this->manager->getUptime());
	}

	/**
	 * Test function to get the eMMC flash memory health
	 */
	public function testGetEmmcHealthOk(): void {
		$this->receiveCommand(
			command: self::COMMANDS['emmcHealth'],
			stdout: 'eMMC Life Time Estimation A [EXT_CSD_DEVICE_LIFE_TIME_EST_TYP_A]: 0x01' . PHP_EOL .
					'eMMC Life Time Estimation B [EXT_CSD_DEVICE_LIFE_TIME_EST_TYP_B]: 0x01' . PHP_EOL .
					'eMMC Pre EOL information [EXT_CSD_PRE_EOL_INFO]: 0x01',
		);
		Assert::same(self::EXPECTED['emmcHealth'], $this->manager->getEmmcHealth());
	}

	/**
	 * Test function to get the eMMC flash memory health when warning is displayed
	 */
	public function testGetEmmcHealthWarning(): void {
		$this->receiveCommand(
			command: self::COMMANDS['emmcHealth'],
			stdout: 'eMMC Life Time Estimation A [EXT_CSD_DEVICE_LIFE_TIME_EST_TYP_A]: 0x08' . PHP_EOL .
					'eMMC Life Time Estimation B [EXT_CSD_DEVICE_LIFE_TIME_EST_TYP_B]: 0x08' . PHP_EOL .
					'eMMC Pre EOL information [EXT_CSD_PRE_EOL_INFO]: 0x02',
		);
		Assert::same([
			'slc_region' => '20 %',
			'mlc_region' => '20 %',
			'status' => 'warning',
		], $this->manager->getEmmcHealth());
	}

	/**
	 * Test function to get the eMMC flash memory health when urgent warning is displayed
	 */
	public function testGetEmmcHealthUrgent(): void {
		$this->receiveCommand(
			command: self::COMMANDS['emmcHealth'],
			stdout: 'eMMC Life Time Estimation A [EXT_CSD_DEVICE_LIFE_TIME_EST_TYP_A]: 0x09' . PHP_EOL .
					'eMMC Life Time Estimation B [EXT_CSD_DEVICE_LIFE_TIME_EST_TYP_B]: 0x09' . PHP_EOL .
					'eMMC Pre EOL information [EXT_CSD_PRE_EOL_INFO]: 0x03',
		);
		Assert::same([
			'slc_region' => '10 %',
			'mlc_region' => '10 %',
			'status' => 'urgent',
		], $this->manager->getEmmcHealth());
	}

	/**
	 * Test function to get the eMMC flash memory health when device does not provide info data and memory is 100% worn off
	 */
	public function testGetEmmcHealthUndefined(): void {
		$this->receiveCommand(
			command: self::COMMANDS['emmcHealth'],
			stdout: 'eMMC Life Time Estimation A [EXT_CSD_DEVICE_LIFE_TIME_EST_TYP_A]: 0x0A' . PHP_EOL .
					'eMMC Life Time Estimation B [EXT_CSD_DEVICE_LIFE_TIME_EST_TYP_B]: 0x0A' . PHP_EOL .
					'eMMC Pre EOL information [EXT_CSD_PRE_EOL_INFO]: 0x00',
		);
		Assert::same([
			'slc_region' => '0 %',
			'mlc_region' => '0 %',
			'status' => 'undefined',
		], $this->manager->getEmmcHealth());
	}

	/**
	 * Test function to get the eMMC flash memory health when eMMC is not present
	 */
	public function testGetEmmcHealthNotPresent(): void {
		$this->receiveCommand(
			command: self::COMMANDS['emmcHealth'],
			stdout: '',
			exitCode: 1,
		);
		Assert::null($this->manager->getEmmcHealth());
	}

	/**
	 * Tests the function to return information about the gateway
	 */
	public function testGet(): void {
		$verbose = false;
		$manager = Mockery::mock(InfoManager::class, [$this->commandExecutor, $this->networkManager, $this->versionManager])->makePartial();
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
		$this->versionManager->shouldReceive('getInfluxdbBridge')
			->andReturn(self::EXPECTED['versions']['influxdbBridge']);
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
		$manager->shouldReceive('getEmmcHealth')
			->andReturn(self::EXPECTED['emmcHealth']);
		Assert::same(self::EXPECTED, $manager->get($verbose));
	}

	/**
	 * Tests the function to return brief information about the gateway
	 */
	public function testGetBrief(): void {
		$manager = Mockery::mock(InfoManager::class, [$this->commandExecutor, $this->networkManager, $this->versionManager])->makePartial();
		$manager->shouldReceive('getBoard')
			->andReturn(self::EXPECTED['board']);
		$manager->shouldReceive('getId')
			->andReturn(self::EXPECTED['gwId']);
		$expected = [
			'board' => self::EXPECTED['board'],
			'gwId' => self::EXPECTED['gwId'],
		];
		Assert::same($expected, $manager->getBrief());
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->setUpCommandExecutor();
		$this->networkManager = Mockery::mock(NetworkManager::class);
		$this->versionManager = Mockery::mock(VersionManager::class);
		$this->manager = new InfoManager($this->commandExecutor, $this->networkManager, $this->versionManager);
	}

}

$test = new InfoManagerTest();
$test->run();
