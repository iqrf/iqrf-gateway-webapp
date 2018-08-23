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
use App\CoreModule\Model\FileManager;
use App\CoreModule\Model\JsonFileManager;
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
	 * @var DevNullStorage Cache storage for testing
	 */
	private $cacheStorage;

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var IqrfAppManager IQRF App manager
	 */
	private $iqrfAppManager;

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
	 * Set up test environment
	 */
	public function setUp() {
		$msgIdManager = new MessageIdManager();
		$this->cacheStorage = new DevNullStorage();
		$commandManager = new CommandManager(false);
		$this->versionManager = new VersionManager($commandManager, $this->cacheStorage);
		$this->iqrfAppManager = new IqrfAppManager($this->wsServer, $msgIdManager);
	}

	/**
	 * Test function to get information about the board (via device tree)
	 */
	public function testGetBoardDeviceTree() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('send')->with($this->commands['deviceTreeName'], true)->andReturn('Raspberry Pi 2 Model B Rev 1.1');
		$manager = new InfoManager($commandManager, $this->iqrfAppManager, $this->versionManager);
		Assert::same('Raspberry Pi 2 Model B Rev 1.1', $manager->getBoard());
	}

	/**
	 * Test function to get information about the board (via DMI)
	 */
	public function testGetBoardDmi() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('send')->with($this->commands['deviceTreeName'], true);
		$commandManager->shouldReceive('send')->with($this->commands['dmiBoardVendor'], true)->andReturn('AAEON');
		$commandManager->shouldReceive('send')->with($this->commands['dmiBoardName'], true)->andReturn('UP-APL01');
		$commandManager->shouldReceive('send')->with($this->commands['dmiBoardVersion'], true)->andReturn('V0.4');
		$manager = new InfoManager($commandManager, $this->iqrfAppManager, $this->versionManager);
		Assert::same('AAEON UP-APL01 (V0.4)', $manager->getBoard());
	}

	/**
	 * Test function to get information about the board (unknown method)
	 */
	public function testGetBoardUnknown() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('send')->with($this->commands['deviceTreeName'], true);
		$commandManager->shouldReceive('send')->with($this->commands['dmiBoardVendor'], true);
		$commandManager->shouldReceive('send')->with($this->commands['dmiBoardName'], true);
		$commandManager->shouldReceive('send')->with($this->commands['dmiBoardVersion'], true);
		$manager = new InfoManager($commandManager, $this->iqrfAppManager, $this->versionManager);
		Assert::same('UNKNOWN', $manager->getBoard());
	}

	/**
	 * Test function to get IPv4 and IPv6 addresses of the gateway
	 */
	public function testGetIpAddresses() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('send')->with($this->commands['networkAdapters'], true)->andReturn('eth0' . PHP_EOL . 'wlan0' . PHP_EOL . 'lo');
		$commandManager->shouldReceive('send')->with($this->commands['ipAddressesEth0'], true)->andReturn('192.168.1.100' . PHP_EOL . 'fda9:d95:d5b1::64');
		$commandManager->shouldReceive('send')->with($this->commands['ipAddressesWlan0'], true)->andReturn('');
		$manager = new InfoManager($commandManager, $this->iqrfAppManager, $this->versionManager);
		Assert::same(['eth0' => ['192.168.1.100', 'fda9:d95:d5b1::64']], $manager->getIpAddresses());
	}

	/**
	 * Test function to get MAC addresses of the gateway
	 */
	public function testGetMacAddresses() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('send')->with($this->commands['networkAdapters'], true)->andReturn('eth0' . PHP_EOL . 'lo');
		$commandManager->shouldReceive('send')->with($this->commands['macAddresses'], true)->andReturn('01:02:03:04:05:06');
		$manager = new InfoManager($commandManager, $this->iqrfAppManager, $this->versionManager);
		Assert::same(['eth0' => '01:02:03:04:05:06'], $manager->getMacAddresses());
	}

	/**
	 * Test function to get hostname of the gateway
	 */
	public function testGetHostname() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$output = 'gateway';
		$commandManager->shouldReceive('send')->with('hostname -f')->andReturn($output);
		$manager = new InfoManager($commandManager, $this->iqrfAppManager, $this->versionManager);
		Assert::same($output, $manager->getHostname());
	}

	/**
	 * Test function to get information about the Coordinator
	 */
	public function testGetCoordinatorInfo() {
		$path = __DIR__ . '/../../data/iqrf/';
		$fileManager = new FileManager($path);
		$jsonFileManager = new JsonFileManager($path);
		$parsedInfo = $jsonFileManager->read('data-os-read');
		$output = [
			'response' => $fileManager->read('response-os-read.json'),
		];
		$iqrfAppManager = \Mockery::mock(IqrfAppManager::class)->makePartial();
		$iqrfAppManager->shouldReceive('sendRaw')->with('00.00.02.00.FF.FF')->andReturn($output);
		$iqrfAppManager->shouldReceive('parseResponse')->with($output)->andReturn($parsedInfo);
		$manager = new InfoManager((new CommandManager(false)), $iqrfAppManager, $this->versionManager);
		Assert::same($parsedInfo, $manager->getCoordinatorInfo());
	}

	/**
	 * Test function to get IQRF Gateway Daemon's version
	 */
	public function testGetDaemonVersion() {
		$version = 'v2.0.0dev 2018-07-04T10:30:51';
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('commandExist')->with('iqrfgd2')->andReturn(true);
		$commandManager->shouldReceive('send')->with($this->commands['daemonVersion'])->andReturn($version);
		$manager = new InfoManager($commandManager, $this->iqrfAppManager, $this->versionManager);
		Assert::same($version, $manager->getDaemonVersion());
	}

	/**
	 * Test function to get IQRF Gateway Daemon's version (IQRF Gateway Daemon is not installed)
	 */
	public function testGetDaemonVersionNotInstalled() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('commandExist')->with('iqrfgd2')->andReturn(false);
		$manager = new InfoManager($commandManager, $this->iqrfAppManager, $this->versionManager);
		Assert::same('none', $manager->getDaemonVersion());
	}

	/**
	 * Test function to get IQRF Gateway Daemon's version (unknown version)
	 */
	public function testGetDaemonVersionUnknown() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('commandExist')->with('iqrfgd2')->andReturn(true);
		$commandManager->shouldReceive('send')->with($this->commands['daemonVersion']);
		$manager = new InfoManager($commandManager, $this->iqrfAppManager, $this->versionManager);
		Assert::same('unknown', $manager->getDaemonVersion());
	}

	/**
	 * Test function to get version of the webapp (with git)
	 */
	public function testGetWebAppVersionWithGit() {
		$expected = 'v1.1.6 (master - 733d45340cbb2565fd068ca3257ad39a5e46f963)';
		$gitBranches = '* master                 733d45340cbb2565fd068ca3257ad39a5e46f963 Add a notification to an update webapp to newer stable version';
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('commandExist')->with('git')->andReturn(true);
		$commandManager->shouldReceive('send')->with($this->commands['gitBranches'])->andReturn($gitBranches);
		$versionManager = new VersionManager($commandManager, $this->cacheStorage);
		$manager = new InfoManager($commandManager, $this->iqrfAppManager, $versionManager);
		Assert::same($expected, $manager->getWebAppVersion());
	}

	/**
	 * Test function to get version of the webapp (without git)
	 */
	public function testGetWebAppVersionWithoutGit() {
		$expected = 'v1.1.6';
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('commandExist')->with('git')->andReturn(false);
		$versionManager = new VersionManager($commandManager, $this->cacheStorage);
		$manager = new InfoManager($commandManager, $this->iqrfAppManager, $versionManager);
		Assert::same($expected, $manager->getWebAppVersion());
	}

}

$test = new InfoManagerTest($container);
$test->run();
