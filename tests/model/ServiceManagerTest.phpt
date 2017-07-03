<?php

/**
 * TEST: App\Model\ServiceManager
 * @phpVersion >= 5.6
 * @testCase
 */
use App\Model\ServiceManager;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../bootstrap.php';

class ServiceManagerTest extends TestCase {

	private $container;

	function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * @test
	 * Test function to start iqrf-daemon service
	 */
	public function testStart() {
		$commandManager = \Mockery::mock('App\Model\CommandManager');
		$commandManager->shouldReceive('send')->with('systemctl start iqrf-daemon.service', true)->andReturn(true);
		$serviceManager = new ServiceManager('systemd', $commandManager);
		Assert::true($serviceManager->start());
	}

	/**
	 * @test
	 * Test function to stop iqrf-daemon service
	 */
	public function testStop() {
		$commandManager = \Mockery::mock('App\Model\CommandManager');
		$commandManager->shouldReceive('send')->with('systemctl stop iqrf-daemon.service', true)->andReturn(true);
		$serviceManager = new ServiceManager('systemd', $commandManager);
		Assert::true($serviceManager->stop());
	}

	/**
	 * @test
	 * Test function to restart iqrf-daemon service
	 */
	public function testRestart() {
		$commandManager = \Mockery::mock('App\Model\CommandManager');
		$commandManager->shouldReceive('send')->with('systemctl restart iqrf-daemon.service', true)->andReturn(true);
		$serviceManager = new ServiceManager('systemd', $commandManager);
		Assert::true($serviceManager->restart());
	}

	/**
	 * @test
	 * Test function to get status of iqrf-daemon service
	 */
	public function testGetStatus() {
		$commandManager = \Mockery::mock('App\Model\CommandManager');
		$commandManager->shouldReceive('send')->with('systemctl status iqrf-daemon.service', true)->andReturn(true);
		$serviceManager = new ServiceManager('systemd', $commandManager);
		Assert::true($serviceManager->getStatus());
	}

}

$test = new ServiceManagerTest($container);
$test->run();
