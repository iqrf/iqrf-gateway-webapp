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
	 * @var \Mockery\MockInterface Mocked command manager
	 */
	private $commandManager;

	/**
	 * @var ServiceManager Service manager for supervisord init daemon in a Docker container
	 */
	private $managerDocker;

	/**
	 * @var ServiceManager Service manager for systemD init daemon
	 */
	private $managerSystemD;

	/**
	 * @var ServiceManager Service manager for unknown init daemon
	 */
	private $managerUnknown;

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
		$this->commandManager = \Mockery::mock(CommandManager::class);
		$this->managerDocker = new ServiceManager('docker-supervisor', $this->commandManager);
		$this->managerSystemD = new ServiceManager('systemd', $this->commandManager);
		$this->managerUnknown = new ServiceManager('unknown', $this->commandManager);
	}

	/**
	 * Cleanup the test environment
	 */
	protected function tearDown(): void {
		\Mockery::close();
	}

	/**
	 * Test function to start IQRF Gateway Daemon's service via systemD
	 */
	public function testStartSystemD(): void {
		$expected = 'start';
		$this->commandManager->shouldReceive('send')->with('systemctl start iqrfgd2.service', true)->andReturn($expected);
		Assert::same($expected, $this->managerSystemD->start());
	}

	/**
	 * Test function to start IQRF Gateway Daemon's service via supervisord in Docker container
	 */
	public function testStartDockerSupervisorD(): void {
		$expected = 'start';
		$this->commandManager->shouldReceive('send')->with('supervisorctl start iqrfgd2', true)->andReturn($expected);
		Assert::same($expected, $this->managerDocker->start());
	}

	/**
	 * Test function to start IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testStartUnknown(): void {
		Assert::exception([$this->managerUnknown, 'start'], NotSupportedInitSystemException::class);
	}

	/**
	 * Test function to stop IQRF Gateway Daemon's service via systemD
	 */
	public function testStopSystemD(): void {
		$expected = 'stop';
		$this->commandManager->shouldReceive('send')->with('systemctl stop iqrfgd2.service', true)->andReturn($expected);
		Assert::same($expected, $this->managerSystemD->stop());
	}

	/**
	 * Test function to stop IQRF Gateway Daemon's service via supervisord in Docker container
	 */
	public function testStopDockerSupervisorD(): void {
		$expected = 'stop';
		$this->commandManager->shouldReceive('send')->with('supervisorctl stop iqrfgd2', true)->andReturn($expected);
		Assert::same($expected, $this->managerDocker->stop());
	}

	/**
	 * Test function to stop IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testStopUnknown(): void {
		Assert::exception([$this->managerUnknown, 'stop'], NotSupportedInitSystemException::class);
	}

	/**
	 * Test function to restart IQRF Gateway Daemon's service via systemD
	 */
	public function testRestartSystemD(): void {
		$expected = 'restart';
		$this->commandManager->shouldReceive('send')->with('systemctl restart iqrfgd2.service', true)->andReturn($expected);
		Assert::same($expected, $this->managerSystemD->restart());
	}

	/**
	 * Test function to restart IQRF Gateway Daemon's service via supervisord in Docker container
	 */
	public function testRestartDockerSupervisorD(): void {
		$expected = 'restart';
		$this->commandManager->shouldReceive('send')->with('supervisorctl restart iqrfgd2', true)->andReturn($expected);
		Assert::same($expected, $this->managerDocker->restart());
	}

	/**
	 * Test function to restart IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testRestartUnknown(): void {
		Assert::exception([$this->managerUnknown, 'restart'], NotSupportedInitSystemException::class);
	}

	/**
	 * Test function to get status of IQRF Gateway Daemon's service via systemD
	 */
	public function testGetStatusSystemD(): void {
		$expected = 'status';
		$this->commandManager->shouldReceive('send')->with('systemctl status iqrfgd2.service', true)->andReturn($expected);
		Assert::same($expected, $this->managerSystemD->getStatus());
	}

	/**
	 * Test function to get status of IQRF Gateway Daemon's service via supervisord in Docker container
	 */
	public function testGetStatusDockerSupervisorD(): void {
		$expected = 'status';
		$this->commandManager->shouldReceive('send')->with('supervisorctl status iqrfgd2', true)->andReturn($expected);
		Assert::same($expected, $this->managerDocker->getStatus());
	}

	/**
	 * Test function to get status of IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testGetStatusUnknown(): void {
		Assert::exception([$this->managerUnknown, 'getStatus'], NotSupportedInitSystemException::class);
	}

}

$test = new ServiceManagerTest($container);
$test->run();
