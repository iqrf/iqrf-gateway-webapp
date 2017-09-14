<?php

/**
 * TEST: App\Model\GwInfoManager
 * @phpVersion >= 5.6
 * @testCase
 */

namespace Test\Model;

use App\IqrfAppModule\Model\CoordinatorParser;
use App\IqrfAppModule\Model\IqrfAppManager;
use App\IqrfAppModule\Model\OsParser;
use App\Model\CommandManager;
use App\Model\GwInfoManager;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../bootstrap.php';

class GwInfoManagerTest extends TestCase {

	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var CoordinatorParser
	 */
	private $coordinatorParser;

	/**
	 * @var OsParser
	 */
	private $osParser;

	/**
	 * Constructor
	 * @param Container $container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * Set up test environment
	 */
	public function setUp() {
		$this->coordinatorParser = new CoordinatorParser();
		$this->osParser = new OsParser();
	}

	/**
	 * @test
	 * Test function to get IPv4 and IPv6 addresses of the gateway
	 */
	public function testGetIpAddresses() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('send')->with('ls /sys/class/net | awk \'{ print $0 }\'', true)->andReturn('eth0' . PHP_EOL . 'wlan0' . PHP_EOL . 'lo');
		$cmdEth0 = 'ip a s eth0 | grep inet | grep global | grep -v temporary | awk \'{print $2}\'';
		$commandManager->shouldReceive('send')->with($cmdEth0, true)->andReturn('192.168.1.100' . PHP_EOL . 'fda9:d95:d5b1::64');
		$cmdWlan0 = 'ip a s wlan0 | grep inet | grep global | grep -v temporary | awk \'{print $2}\'';
		$commandManager->shouldReceive('send')->with($cmdWlan0, true)->andReturn('');
		$iqrfAppManager = new IqrfAppManager($commandManager, $this->coordinatorParser, $this->osParser);
		$gwInfoManager = new GwInfoManager($commandManager, $iqrfAppManager);
		Assert::same(['eth0' => ['192.168.1.100', 'fda9:d95:d5b1::64']], $gwInfoManager->getIpAddresses());
	}

	/**
	 * @test
	 * Test function to get MAC addresses of the gateway
	 */
	public function testGetMacAddresses() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('send')->with('ls /sys/class/net | awk \'{ print $0 }\'', true)->andReturn('eth0' . PHP_EOL . 'lo');
		$commandManager->shouldReceive('send')->with('cat /sys/class/net/eth0/address', true)->andReturn('01:02:03:04:05:06');
		$iqrfAppManager = new IqrfAppManager($commandManager, $this->coordinatorParser, $this->osParser);
		$gwInfoManager = new GwInfoManager($commandManager, $iqrfAppManager);
		Assert::same(['eth0' => '01:02:03:04:05:06'], $gwInfoManager->getMacAddresses());
	}

	/**
	 * @test
	 * Test function to get hostname of the gateway
	 */
	public function testGetHostname() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$output = 'gateway';
		$commandManager->shouldReceive('send')->with('hostname -f')->andReturn($output);
		$iqrfAppManager = new IqrfAppManager($commandManager, $this->coordinatorParser, $this->osParser);
		$gwInfoManager = new GwInfoManager($commandManager, $iqrfAppManager);
		Assert::same($output, $gwInfoManager->getHostname());
	}

	/**
	 * @test
	 * Test function to get information about the Coordinator
	 */
	public function testGetCoordinatorInfo() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$cmd = 'iqrfapp "{\"ctype\":\"dpa\",\"type\":\"raw\",\"msgid\":\"1\",\"timeout\":1000,\"request\":\"00.00.02.00.FF.FF\"}"';
		$commandManager->shouldReceive('send')->with($cmd, true)->andReturn(null);
		$iqrfAppManager = new IqrfAppManager($commandManager, $this->coordinatorParser, $this->osParser);
		$gwInfoManager = new GwInfoManager($commandManager, $iqrfAppManager);
		Assert::null($gwInfoManager->getCoordinatorInfo());
	}

	/**
	 * @test
	 * Test function to get version of the daemon
	 */
	public function testGetDaemonVersion() {
		$version = '0.7.0-1';
		$commandManager0 = \Mockery::mock(CommandManager::class);
		$commandManager0->shouldReceive('commandExist')->with('iqrfapp')->andReturn(false);
		$iqrfAppManager0 = new IqrfAppManager($commandManager0, $this->coordinatorParser, $this->osParser);
		$gwInfoManager0 = new GwInfoManager($commandManager0, $iqrfAppManager0);
		Assert::same('none', $gwInfoManager0->getDaemonVersion());
		$commandManager1 = \Mockery::mock(CommandManager::class);
		$commandManager1->shouldReceive('commandExist')->with('iqrfapp')->andReturn(true);
		$commandManager1->shouldReceive('commandExist')->with('apt-cache')->andReturn(true);
		$commandManager1->shouldReceive('send')->with('apt-cache madison iqrf-daemon | awk \'{ print $3 }\'')->andReturn($version);
		$iqrfAppManager1 = new IqrfAppManager($commandManager1, $this->coordinatorParser, $this->osParser);
		$gwInfoManager1 = new GwInfoManager($commandManager1, $iqrfAppManager1);
		Assert::same($version, $gwInfoManager1->getDaemonVersion());
		$commandManager2 = \Mockery::mock(CommandManager::class);
		$commandManager2->shouldReceive('commandExist')->with('iqrfapp')->andReturn(true);
		$commandManager2->shouldReceive('commandExist')->with('apt-cache')->andReturn(false);
		$iqrfAppManager2 = new IqrfAppManager($commandManager2, $this->coordinatorParser, $this->osParser);
		$gwInfoManager2 = new GwInfoManager($commandManager2, $iqrfAppManager2);
		Assert::same('unknown', $gwInfoManager2->getDaemonVersion());
	}

}

$test = new GwInfoManagerTest($container);
$test->run();
