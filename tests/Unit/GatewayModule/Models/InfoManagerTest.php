<?php

/**
 * TEST: App\GatewayModule\Models\InfoManager
 * @covers App\GatewayModule\Models\InfoManager
 * @phpVersion >= 7.2
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\GatewayModule\Models;

use App\GatewayModule\Models\InfoManager;
use App\GatewayModule\Models\NetworkManager;
use App\GatewayModule\Models\VersionManager;
use App\IqrfNetModule\Models\EnumerationManager;
use Mockery;
use Mockery\MockInterface;
use Tester\Assert;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for Gateway info manager
 */
final class InfoManagerTest extends CommandTestCase {

	/**
	 * @var MockInterface|EnumerationManager Mocked IQMESH enumeration manager
	 */
	private $enumerationManager;

	/**
	 * @var MockInterface|NetworkManager Mocked network manager
	 */
	private $networkManager;

	/**
	 * @var InfoManager Gateway Info manager with mocked command manager
	 */
	private $manager;

	/**
	 * @var MockInterface|VersionManager Mocked version manager
	 */
	private $versionManager;

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
		'pixlaToken' => 'cat /etc/gwman/customer_id',
	];

	/**
	 * Expected outputs
	 */
	private const EXPECTED = [
		'board' => 'MICRORISC s.r.o. IQD-GW-01',
		'gwId' => '0242fc1e6f85b296',
		'pixla' => null,
		'versions' => [
			'controller' => 'v1.0.0',
			'daemon' => 'v2.3.0',
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
	 * Tests the function to get the gateway ID (missing file)
	 */
	public function testGetIdMissingFile(): void {
		$stderr = 'cat: /etc/iqrf-gateway.json: No such file or directory';
		$this->receiveCommand(self::COMMANDS['gw'], true, '', $stderr, 1);
		Assert::null($this->manager->getId());
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
	 * Tests the function to get information about the Coordinator
	 */
	public function testGetCoordinatorInfo(): void {
		$expected = ['request' => [], 'response' => []];
		$this->enumerationManager->shouldReceive('device')->with(0)->andReturn($expected);
		Assert::same($expected, $this->manager->getCoordinatorInfo());
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
	 * Tests the function to return PIXLA token (failure)
	 */
	public function testGetPixlaTokenFailure(): void {
		$this->receiveCommand(self::COMMANDS['pixlaToken'], true);
		Assert::null($this->manager->getPixlaToken());
	}

	/**
	 * Tests the function to return PIXLA token (success)
	 */
	public function testGetPixlaTokenSuccess(): void {
		$token = 'secretPixlaToken';
		$this->receiveCommand(self::COMMANDS['pixlaToken'], true, $token);
		Assert::same($token, $this->manager->getPixlaToken());
	}

	/**
	 * Tests the function to return information about the gateway
	 */
	public function testGet(): void {
		$verbose = false;
		$manager = Mockery::mock(InfoManager::class, [$this->commandManager, $this->enumerationManager, $this->networkManager, $this->versionManager])->makePartial();
		$manager->shouldReceive('getBoard')
			->andReturn(self::EXPECTED['board']);
		$manager->shouldReceive('getId')
			->andReturn(self::EXPECTED['gwId']);
		$manager->shouldReceive('getPixlaToken')
			->andReturn(self::EXPECTED['pixla']);
		$this->versionManager->shouldReceive('getController')
			->andReturn(self::EXPECTED['versions']['controller']);
		$this->versionManager->shouldReceive('getDaemon')
			->withArgs([$verbose])
			->andReturn(self::EXPECTED['versions']['daemon']);
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
		Assert::same(self::EXPECTED, $manager->get($verbose));
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->enumerationManager = Mockery::mock(EnumerationManager::class);
		$this->networkManager = Mockery::mock(NetworkManager::class);
		$this->versionManager = Mockery::mock(VersionManager::class);
		$this->manager = new InfoManager($this->commandManager, $this->enumerationManager, $this->networkManager, $this->versionManager);
	}

}

$test = new InfoManagerTest();
$test->run();
