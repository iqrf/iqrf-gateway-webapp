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
use App\Model\CommandManager;
use App\Model\FileManager;
use App\Model\JsonFileManager;
use App\Model\VersionManager;
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
	 * Test function to get board info
	 */
	public function testGetBoard() {
		$commandManager0 = \Mockery::mock(CommandManager::class);
		$commandManager0->shouldReceive('send')->with($this->commands['deviceTreeName'], true)->andReturn('Raspberry Pi 2 Model B Rev 1.1');
		$gwInfoManager0 = new InfoManager($commandManager0, $this->iqrfAppManager, $this->versionManager);
		Assert::same('Raspberry Pi 2 Model B Rev 1.1', $gwInfoManager0->getBoard());
		$commandManager1 = \Mockery::mock(CommandManager::class);
		$commandManager1->shouldReceive('send')->with($this->commands['deviceTreeName'], true);
		$commandManager1->shouldReceive('send')->with($this->commands['dmiBoardVendor'], true)->andReturn('AAEON');
		$commandManager1->shouldReceive('send')->with($this->commands['dmiBoardName'], true)->andReturn('UP-APL01');
		$commandManager1->shouldReceive('send')->with($this->commands['dmiBoardVersion'], true)->andReturn('V0.4');
		$gwInfoManager1 = new InfoManager($commandManager1, $this->iqrfAppManager, $this->versionManager);
		Assert::same('AAEON UP-APL01 (V0.4)', $gwInfoManager1->getBoard());
		$commandManager2 = \Mockery::mock(CommandManager::class);
		$commandManager2->shouldReceive('send')->with($this->commands['deviceTreeName'], true);
		$commandManager2->shouldReceive('send')->with($this->commands['dmiBoardVendor'], true);
		$commandManager2->shouldReceive('send')->with($this->commands['dmiBoardName'], true);
		$commandManager2->shouldReceive('send')->with($this->commands['dmiBoardVersion'], true);
		$gwInfoManager2 = new InfoManager($commandManager2, $this->iqrfAppManager, $this->versionManager);
		Assert::same('UNKNOWN', $gwInfoManager2->getBoard());
	}

	/**
	 * Test function to get IPv4 and IPv6 addresses of the gateway
	 */
	public function testGetIpAddresses() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('send')->with($this->commands['networkAdapters'], true)->andReturn('eth0' . PHP_EOL . 'wlan0' . PHP_EOL . 'lo');
		$commandManager->shouldReceive('send')->with($this->commands['ipAddressesEth0'], true)->andReturn('192.168.1.100' . PHP_EOL . 'fda9:d95:d5b1::64');
		$commandManager->shouldReceive('send')->with($this->commands['ipAddressesWlan0'], true)->andReturn('');
		$gwInfoManager = new InfoManager($commandManager, $this->iqrfAppManager, $this->versionManager);
		Assert::same(['eth0' => ['192.168.1.100', 'fda9:d95:d5b1::64']], $gwInfoManager->getIpAddresses());
	}

	/**
	 * Test function to get MAC addresses of the gateway
	 */
	public function testGetMacAddresses() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('send')->with($this->commands['networkAdapters'], true)->andReturn('eth0' . PHP_EOL . 'lo');
		$commandManager->shouldReceive('send')->with($this->commands['macAddresses'], true)->andReturn('01:02:03:04:05:06');
		$gwInfoManager = new InfoManager($commandManager, $this->iqrfAppManager, $this->versionManager);
		Assert::same(['eth0' => '01:02:03:04:05:06'], $gwInfoManager->getMacAddresses());
	}

	/**
	 * Test function to get hostname of the gateway
	 */
	public function testGetHostname() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$output = 'gateway';
		$commandManager->shouldReceive('send')->with('hostname -f')->andReturn($output);
		$gwInfoManager = new InfoManager($commandManager, $this->iqrfAppManager, $this->versionManager);
		Assert::same($output, $gwInfoManager->getHostname());
	}

	/**
	 * Test function to get information about the Coordinator
	 */
	public function testGetCoordinatorInfo() {
		$fileManager = new FileManager(__DIR__ . '/../../IqrfAppModule/model/data/');
		$jsonFileManager = new JsonFileManager(__DIR__ . '/../../IqrfAppModule/model/data/');
		$parsedInfo = $jsonFileManager->read('data-os-read');
		$output = [
			'response' => $fileManager->read('response-os-read.json'),
		];
		$iqrfAppManager = \Mockery::mock(IqrfAppManager::class)->makePartial();
		$iqrfAppManager->shouldReceive('sendRaw')->with('00.00.02.00.FF.FF')->andReturn($output);
		$iqrfAppManager->shouldReceive('parseResponse')->with($output)->andReturn($parsedInfo);
		$gwInfoManager = new InfoManager((new CommandManager(false)), $iqrfAppManager, $this->versionManager);
		Assert::same($parsedInfo, $gwInfoManager->getCoordinatorInfo());
	}

	/**
	 * Test function to get version of the daemon
	 */
	public function testGetDaemonVersion() {
		$version = 'v2.0.0dev 2018-07-04T10:30:51';
		$commandManager0 = \Mockery::mock(CommandManager::class);
		$commandManager0->shouldReceive('commandExist')->with('iqrfgd2')->andReturn(false);
		$gwInfoManager0 = new InfoManager($commandManager0, $this->iqrfAppManager, $this->versionManager);
		Assert::same('none', $gwInfoManager0->getDaemonVersion());
		$commandManager1 = \Mockery::mock(CommandManager::class);
		$commandManager1->shouldReceive('commandExist')->with('iqrfgd2')->andReturn(true);
		$commandManager1->shouldReceive('send')->with($this->commands['daemonVersion'])->andReturn($version);
		$gwInfoManager1 = new InfoManager($commandManager1, $this->iqrfAppManager, $this->versionManager);
		Assert::same($version, $gwInfoManager1->getDaemonVersion());
		$commandManager2 = \Mockery::mock(CommandManager::class);
		$commandManager2->shouldReceive('commandExist')->with('iqrfgd2')->andReturn(true);
		$commandManager2->shouldReceive('send')->with($this->commands['daemonVersion']);
		$gwInfoManager2 = new InfoManager($commandManager2, $this->iqrfAppManager, $this->versionManager);
		Assert::same('unknown', $gwInfoManager2->getDaemonVersion());
	}

	/**
	 * Test function to get version of the webapp
	 */
	public function testGetWebAppVersion() {
		$version = 'v1.1.6';
		$commandManager0 = \Mockery::mock(CommandManager::class);
		$commandManager0->shouldReceive('commandExist')->with('git')->andReturn(false);
		$versionManager0 = new VersionManager($commandManager0, $this->cacheStorage);
		$gwInfoManager0 = new InfoManager($commandManager0, $this->iqrfAppManager, $versionManager0);
		Assert::same($version, $gwInfoManager0->getWebAppVersion());
		$gitBranches = '* master                 733d45340cbb2565fd068ca3257ad39a5e46f963 Add a notification to an update webapp to newer stable version';
		$commandManager1 = \Mockery::mock(CommandManager::class);
		$commandManager1->shouldReceive('commandExist')->with('git')->andReturn(true);
		$commandManager1->shouldReceive('send')->with($this->commands['gitBranches'])->andReturn($gitBranches);
		$versionManager1 = new VersionManager($commandManager1, $this->cacheStorage);
		$gwInfoManager1 = new InfoManager($commandManager1, $this->iqrfAppManager, $versionManager1);
		Assert::same($version . ' (master - 733d45340cbb2565fd068ca3257ad39a5e46f963)', $gwInfoManager1->getWebAppVersion());
	}

}

$test = new InfoManagerTest($container);
$test->run();
