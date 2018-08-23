<?php

/**
 * TEST: App\ServiceModule\Model\ServiceManager
 * @covers App\ServiceModule\Model\ServiceManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\ServiceModule\Model;

use App\CoreModule\Model\CommandManager;
use App\ServiceModule\Exception\NotSupportedInitSystemException;
use App\ServiceModule\Model\ServiceManager;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for service manager
 */
class ServiceManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

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
		$this->commandManager = new CommandManager(false);
	}

	/**
	 * Test function to start IQRF Gateway Daemon's service via systemD
	 */
	public function testStartSystemD() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('send')->with('systemctl start iqrfgd2.service', true)->andReturn(true);
		$manager = new ServiceManager('systemd', $commandManager);
		Assert::true($manager->start());
	}

	/**
	 * Test function to start IQRF Gateway Daemon's service via supervisord in Docker container
	 */
	public function testStartDockerSupervisorD() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('send')->with('supervisorctl start iqrfgd2', true)->andReturn(true);
		$manager = new ServiceManager('docker-supervisor', $commandManager);
		Assert::true($manager->start());
	}

	/**
	 * Test function to start IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testStartUnknown() {
		Assert::exception(function () {
			$manager = new ServiceManager('unknown', $this->commandManager);
			$manager->start();
		}, NotSupportedInitSystemException::class);
	}

	/**
	 * Test function to stop IQRF Gateway Daemon's service via systemD
	 */
	public function testStopSystemD() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('send')->with('systemctl stop iqrfgd2.service', true)->andReturn(true);
		$manager = new ServiceManager('systemd', $commandManager);
		Assert::true($manager->stop());
	}

	/**
	 * Test function to stop IQRF Gateway Daemon's service via supervisord in Docker container
	 */
	public function testStopDockerSupervisorD() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('send')->with('supervisorctl stop iqrfgd2', true)->andReturn(true);
		$manager = new ServiceManager('docker-supervisor', $commandManager);
		Assert::true($manager->stop());
	}

	/**
	 * Test function to stop IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testStopUnknown() {
		Assert::exception(function () {
			$manager = new ServiceManager('unknown', $this->commandManager);
			$manager->stop();
		}, NotSupportedInitSystemException::class);
	}

	/**
	 * Test function to restart IQRF Gateway Daemon's service via systemD
	 */
	public function testRestartSystemD() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('send')->with('systemctl restart iqrfgd2.service', true)->andReturn(true);
		$manager = new ServiceManager('systemd', $commandManager);
		Assert::true($manager->restart());
	}

	/**
	 * Test function to restart IQRF Gateway Daemon's service via supervisord in Docker container
	 */
	public function testRestartDockerSupervisorD() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('send')->with('supervisorctl restart iqrfgd2', true)->andReturn(true);
		$manager = new ServiceManager('docker-supervisor', $commandManager);
		Assert::true($manager->restart());
	}

	/**
	 * Test function to restart IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testRestartUnknown() {
		Assert::exception(function () {
			$manager = new ServiceManager('unknown', $this->commandManager);
			$manager->restart();
		}, NotSupportedInitSystemException::class);
	}

	/**
	 * Test function to get status of IQRF Gateway Daemon's service via systemD
	 */
	public function testGetStatusSystemD() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('send')->with('systemctl status iqrfgd2.service', true)->andReturn(true);
		$manager = new ServiceManager('systemd', $commandManager);
		Assert::true($manager->getStatus());
	}

	/**
	 * Test function to get status of IQRF Gateway Daemon's service via supervisord in Docker container
	 */
	public function testGetStatusDockerSupervisorD() {
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('send')->with('supervisorctl status iqrfgd2', true)->andReturn(true);
		$manager = new ServiceManager('docker-supervisor', $commandManager);
		Assert::true($manager->getStatus());
	}

	/**
	 * Test function to get status of IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testGetStatusUnknown() {
		Assert::exception(function () {
			$manager = new ServiceManager('unknown', $this->commandManager);
			$manager->getStatus();
		}, NotSupportedInitSystemException::class);
	}

}

$test = new ServiceManagerTest($container);
$test->run();
