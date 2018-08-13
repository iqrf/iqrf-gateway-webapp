<?php

/**
 * TEST: App\ServiceModule\Model\ServiceManager
 * @covers App\ServiceModule\Model\ServiceManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\ServiceModule\Model;

use App\Model\CommandManager;
use App\ServiceModule\Model\NotSupportedInitSystemException;
use App\ServiceModule\Model\ServiceManager;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

class ServiceManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * Constructor
	 * @param Container $container Nette Tester Container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * Test function to start iqrfgd2 service
	 */
	public function testStart() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('send')->with('systemctl start iqrfgd2.service', true)->andReturn(true);
		$commandManager->shouldReceive('send')->with('supervisorctl start iqrfgd2', true)->andReturn(true);
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
	 * Test function to stop iqrfgd2 service
	 */
	public function testStop() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('send')->with('systemctl stop iqrfgd2.service', true)->andReturn(true);
		$commandManager->shouldReceive('send')->with('supervisorctl stop iqrfgd2', true)->andReturn(true);
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
	 * Test function to restart iqrfgd2 service
	 */
	public function testRestart() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('send')->with('systemctl restart iqrfgd2.service', true)->andReturn(true);
		$commandManager->shouldReceive('send')->with('supervisorctl restart iqrfgd2', true)->andReturn(true);
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
	 * Test function to get status of iqrfgd2 service
	 */
	public function testGetStatus() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('send')->with('systemctl status iqrfgd2.service', true)->andReturn(true);
		$commandManager->shouldReceive('send')->with('supervisorctl status iqrfgd2', true)->andReturn(true);
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
