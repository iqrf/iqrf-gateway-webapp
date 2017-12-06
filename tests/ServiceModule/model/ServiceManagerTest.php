<?php

/**
 * TEST: App\ServiceModule\Model\ServiceManager
 * @covers App\ServiceModule\Model\ServiceManager
 * @phpVersion >= 5.6
 * @testCase
 */

namespace Test\ServiceModule\Model;

use App\Model\CommandManager;
use App\ServiceModule\Model\NotSupportedInitSystemException;
use App\ServiceModule\Model\ServiceManager;
use Nette\DI\Container;
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
		$commandManager->shouldReceive('send')->with('supervisorctl start iqrf-daemon', true)->andReturn(true);
		$managerSystemD = new ServiceManager('systemd', $commandManager);
		Assert::true($managerSystemD->start());
		$managerDockerSupervisor = new ServiceManager('docker-supervisor', $commandManager);
		Assert::true($managerDockerSupervisor->start());
		Assert::exception(function () use ($commandManager) {
			$managerUnknown = new ServiceManager('unknown', $commandManager);
			$managerUnknown->start();
		}, NotSupportedInitSystemException::class);
	}

	/**
	 * @test
	 * Test function to stop iqrf-daemon service
	 */
	public function testStop() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('send')->with('systemctl stop iqrf-daemon.service', true)->andReturn(true);
		$commandManager->shouldReceive('send')->with('supervisorctl stop iqrf-daemon', true)->andReturn(true);
		$managerSystemD = new ServiceManager('systemd', $commandManager);
		Assert::true($managerSystemD->stop());
		$managerDockerSupervisor = new ServiceManager('docker-supervisor', $commandManager);
		Assert::true($managerDockerSupervisor->stop());
		Assert::exception(function () use ($commandManager) {
			$managerUnknown = new ServiceManager('unknown', $commandManager);
			$managerUnknown->stop();
		}, NotSupportedInitSystemException::class);
	}

	/**
	 * @test
	 * Test function to restart iqrf-daemon service
	 */
	public function testRestart() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('send')->with('systemctl restart iqrf-daemon.service', true)->andReturn(true);
		$commandManager->shouldReceive('send')->with('supervisorctl restart iqrf-daemon', true)->andReturn(true);
		$managerSystemD = new ServiceManager('systemd', $commandManager);
		Assert::true($managerSystemD->restart());
		$managerDockerSupervisor = new ServiceManager('docker-supervisor', $commandManager);
		Assert::true($managerDockerSupervisor->restart());
		Assert::exception(function () use ($commandManager) {
			$managerUnknown = new ServiceManager('unknown', $commandManager);
			$managerUnknown->restart();
		}, NotSupportedInitSystemException::class);
	}

	/**
	 * @test
	 * Test function to get status of iqrf-daemon service
	 */
	public function testGetStatus() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('send')->with('systemctl status iqrf-daemon.service', true)->andReturn(true);
		$commandManager->shouldReceive('send')->with('supervisorctl status iqrf-daemon', true)->andReturn(true);
		$managerSystemD = new ServiceManager('systemd', $commandManager);
		Assert::true($managerSystemD->getStatus());
		$managerDockerSupervisor = new ServiceManager('docker-supervisor', $commandManager);
		Assert::true($managerDockerSupervisor->getStatus());
		Assert::exception(function () use ($commandManager) {
			$managerUnknown = new ServiceManager('unknown', $commandManager);
			$managerUnknown->getStatus();
		}, NotSupportedInitSystemException::class);
	}

}

$test = new ServiceManagerTest($container);
$test->run();
