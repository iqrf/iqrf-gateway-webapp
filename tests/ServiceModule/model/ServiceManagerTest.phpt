<?php

/**
 * TEST: App\ServiceModule\Model\ServiceManager
 * @phpVersion >= 5.6
 * @testCase
 */

namespace Test\ServiceModule\Model;

use App\Model\CommandManager;
use App\ServiceModule\Model\ServiceManager;
use Nette\DI\Container;
use Nette\NotImplementedException;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

class ServiceManagerTest extends TestCase {

	private $container;

	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * @test
	 * Test function to start iqrf-daemon service
	 */
	public function testStart() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('send')->with('systemctl start iqrf-daemon.service', true)->andReturn(true);
		$serviceManager = new ServiceManager('systemd', $commandManager);
		Assert::true($serviceManager->start());
		Assert::exception(function () use ($commandManager) {
			$serviceManager = new ServiceManager('unknown', $commandManager);
			$serviceManager->start();
		}, NotImplementedException::class);
	}

	/**
	 * @test
	 * Test function to stop iqrf-daemon service
	 */
	public function testStop() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('send')->with('systemctl stop iqrf-daemon.service', true)->andReturn(true);
		$serviceManager = new ServiceManager('systemd', $commandManager);
		Assert::true($serviceManager->stop());
		Assert::exception(function () use ($commandManager) {
			$serviceManager = new ServiceManager('unknown', $commandManager);
			$serviceManager->stop();
		}, NotImplementedException::class);
	}

	/**
	 * @test
	 * Test function to restart iqrf-daemon service
	 */
	public function testRestart() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('send')->with('systemctl restart iqrf-daemon.service', true)->andReturn(true);
		$serviceManager = new ServiceManager('systemd', $commandManager);
		Assert::true($serviceManager->restart());
		Assert::exception(function () use ($commandManager) {
			$serviceManager = new ServiceManager('unknown', $commandManager);
			$serviceManager->restart();
		}, NotImplementedException::class);
	}

	/**
	 * @test
	 * Test function to get status of iqrf-daemon service
	 */
	public function testGetStatus() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('send')->with('systemctl status iqrf-daemon.service', true)->andReturn(true);
		$serviceManager = new ServiceManager('systemd', $commandManager);
		Assert::true($serviceManager->getStatus());
		Assert::exception(function () use ($commandManager) {
			$serviceManager = new ServiceManager('unknown', $commandManager);
			$serviceManager->getStatus();
		}, NotImplementedException::class);
	}

}

$test = new ServiceManagerTest($container);
$test->run();
