<?php

/**
 * TEST: App\GatewayModule\Model\InfoManager
 * @covers App\GatewayModule\Model\InfoManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\Model;

use App\GatewayModule\Model\InfoManager;
use App\IqrfAppModule\Model\IqrfAppManager;
use App\IqrfAppModule\Model\MessageIdManager;
use App\CoreModule\Model\CommandManager;
use App\CoreModule\Model\VersionManager;
use Nette\Caching\Storages\DevNullStorage;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for Gateway info manager
 */
class InfoManagerTest extends TestCase {

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * @var \Mockery\MockInterface Mocked command manager
	 */
	private $commandManagerMocked;

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var IqrfAppManager IQRF App manager
	 */
	private $iqrfAppManager;

	/**
	 * @var InfoManager Gateway Info manager with mocked command manager
	 */
	private $managerMocked;

	/**
	 * @var VersionManager Version manager
	 */
	private $versionManager;

	/**
	 * @var string URL to IQRF Gateway Daemon's WebSocket server
	 */
	private $wsServer = 'ws://echo.socketo.me:9000';

	/**
	 * @var array Mocked commands
	 */
	private $commands = [
		'daemonVersion' => 'iqrfgd2 version',
		'deviceTreeName' => 'cat /proc/device-tree/model',
		'dmiBoardName' => 'cat /sys/class/dmi/id/board_name',
		'dmiBoardVendor' => 'cat /sys/class/dmi/id/board_vendor',
		'dmiBoardVersion' => 'cat /sys/class/dmi/id/board_version',
		'ipAddressesEth0' => 'ip a s eth0 | grep inet | grep global | grep -v temporary | awk \'{print $2}\'',
		'ipAddressesWlan0' => 'ip a s wlan0 | grep inet | grep global | grep -v temporary | awk \'{print $2}\'',
		'macAddresses' => 'cat /sys/class/net/eth0/address',
		'networkAdapters' => 'ls /sys/class/net | awk \'{ print $0 }\'',
		'gitBranches' => 'git branch -v --no-abbrev',
	];

	/**
	 * Constructor
	 * @param Container $container Nette Tester Container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * Set up the test environment
	 */
	protected function setUp(): void {
		$msgIdManager = new MessageIdManager();
		$cacheStorage = new DevNullStorage();
		$this->commandManager = new CommandManager(false);
		$this->commandManagerMocked = \Mockery::mock(CommandManager::class);
		$this->versionManager = new VersionManager($this->commandManager, $cacheStorage);
		$this->iqrfAppManager = new IqrfAppManager($this->wsServer, $msgIdManager);
		$this->managerMocked = new InfoManager($this->commandManagerMocked, $this->iqrfAppManager, $this->versionManager);
	}

	/**
	 * Cleanup the test environment
	 */
	protected function tearDown(): void {
		\Mockery::close();
	}

	/**
	 * Test function to get information about the board (via device tree)
	 */
	public function testGetBoardDeviceTree(): void {
		$expected = 'Raspberry Pi 2 Model B Rev 1.1';
		$this->commandManagerMocked->shouldReceive('send')->with($this->commands['deviceTreeName'], true)->andReturn($expected);
		Assert::same($expected, $this->managerMocked->getBoard());
	}

	/**
	 * Test function to get information about the board (via DMI)
	 */
	public function testGetBoardDmi(): void {
		$this->commandManagerMocked->shouldReceive('send')->with($this->commands['deviceTreeName'], true);
		$this->commandManagerMocked->shouldReceive('send')->with($this->commands['dmiBoardVendor'], true)->andReturn('AAEON');
		$this->commandManagerMocked->shouldReceive('send')->with($this->commands['dmiBoardName'], true)->andReturn('UP-APL01');
		$this->commandManagerMocked->shouldReceive('send')->with($this->commands['dmiBoardVersion'], true)->andReturn('V0.4');
		Assert::same('AAEON UP-APL01 (V0.4)', $this->managerMocked->getBoard());
	}

	/**
	 * Test function to get information about the board (unknown method)
	 */
	public function testGetBoardUnknown(): void {
		$this->commandManagerMocked->shouldReceive('send')->with($this->commands['deviceTreeName'], true);
		$this->commandManagerMocked->shouldReceive('send')->with($this->commands['dmiBoardVendor'], true);
		$this->commandManagerMocked->shouldReceive('send')->with($this->commands['dmiBoardName'], true);
		$this->commandManagerMocked->shouldReceive('send')->with($this->commands['dmiBoardVersion'], true);
		Assert::same('UNKNOWN', $this->managerMocked->getBoard());
	}

	/**
	 * Test function to get IPv4 and IPv6 addresses of the gateway
	 */
	public function testGetIpAddresses(): void {
		$this->commandManagerMocked->shouldReceive('send')->with($this->commands['networkAdapters'], true)->andReturn('eth0' . PHP_EOL . 'wlan0' . PHP_EOL . 'lo');
		$this->commandManagerMocked->shouldReceive('send')->with($this->commands['ipAddressesEth0'], true)->andReturn('192.168.1.100' . PHP_EOL . 'fda9:d95:d5b1::64');
		$this->commandManagerMocked->shouldReceive('send')->with($this->commands['ipAddressesWlan0'], true)->andReturn('');
		$expected = ['eth0' => ['192.168.1.100', 'fda9:d95:d5b1::64']];
		Assert::same($expected, $this->managerMocked->getIpAddresses());
	}

	/**
	 * Test function to get MAC addresses of the gateway
	 */
	public function testGetMacAddresses(): void {
		$this->commandManagerMocked->shouldReceive('send')->with($this->commands['networkAdapters'], true)->andReturn('eth0' . PHP_EOL . 'lo');
		$this->commandManagerMocked->shouldReceive('send')->with($this->commands['macAddresses'], true)->andReturn('01:02:03:04:05:06');
		$expected = ['eth0' => '01:02:03:04:05:06'];
		Assert::same($expected, $this->managerMocked->getMacAddresses());
	}

	/**
	 * Test function to get hostname of the gateway
	 */
	public function testGetHostname(): void {
		$expected = 'gateway';
		$this->commandManagerMocked->shouldReceive('send')->with('hostname -f')->andReturn($expected);
		Assert::same($expected, $this->managerMocked->getHostname());
	}

	/**
	 * Test function to get information about the Coordinator
	 */
	public function testGetCoordinatorInfo(): void {
		$output = ['response'];
		$expected = ['parsedResponse'];
		$iqrfAppManager = \Mockery::mock(IqrfAppManager::class);
		$iqrfAppManager->shouldReceive('sendRaw')->with('00.00.02.00.FF.FF')->andReturn($output);
		$iqrfAppManager->shouldReceive('parseResponse')->with($output)->andReturn($expected);
		$manager = new InfoManager($this->commandManager, $iqrfAppManager, $this->versionManager);
		Assert::same($expected, $manager->getCoordinatorInfo());
	}

	/**
	 * Test function to get IQRF Gateway Daemon's version
	 */
	public function testGetDaemonVersion(): void {
		$expected = 'v2.0.0dev 2018-07-04T10:30:51';
		$this->commandManagerMocked->shouldReceive('commandExist')->with('iqrfgd2')->andReturn(true);
		$this->commandManagerMocked->shouldReceive('send')->with($this->commands['daemonVersion'])->andReturn($expected);
		Assert::same($expected, $this->managerMocked->getDaemonVersion());
	}

	/**
	 * Test function to get IQRF Gateway Daemon's version (IQRF Gateway Daemon is not installed)
	 */
	public function testGetDaemonVersionNotInstalled(): void {
		$this->commandManagerMocked->shouldReceive('commandExist')->with('iqrfgd2')->andReturn(false);
		Assert::same('none', $this->managerMocked->getDaemonVersion());
	}

	/**
	 * Test function to get IQRF Gateway Daemon's version (unknown version)
	 */
	public function testGetDaemonVersionUnknown(): void {
		$this->commandManagerMocked->shouldReceive('commandExist')->with('iqrfgd2')->andReturn(true);
		$this->commandManagerMocked->shouldReceive('send')->with($this->commands['daemonVersion']);
		Assert::same('unknown', $this->managerMocked->getDaemonVersion());
	}

	/**
	 * Test function to get version of the webapp
	 */
	public function testGetWebAppVersion(): void {
		$expected = 'v1.1.6';
		$versionManager = \Mockery::mock(VersionManager::class);
		$versionManager->shouldReceive('getInstalledWebapp')->andReturn($expected);
		$manager = new InfoManager($this->commandManager, $this->iqrfAppManager, $versionManager);
		Assert::same($expected, $manager->getWebAppVersion());
	}

}

$test = new InfoManagerTest($container);
$test->run();
