<?php

/**
 * TEST: App\ServiceModule\Models\ServiceManager
 * @covers App\ServiceModule\Models\ServiceManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\ServiceModule\Models;

use App\CoreModule\Models\CommandManager;
use App\ServiceModule\Exceptions\NotSupportedInitSystemException;
use App\ServiceModule\Models\ServiceManager;
use Mockery;
use Mockery\MockInterface;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for service manager
 */
class ServiceManagerTest extends TestCase {

	/**
	 * @var MockInterface Mocked command manager
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
	 * @var string Name of service
	 */
	private $serviceName = 'iqrf-gateway-daemon';

	/**
	 * Test function to start IQRF Gateway Daemon's service via systemD
	 */
	public function testStartSystemD(): void {
		$expected = 'start';
		$command = 'systemctl start ' . $this->serviceName . '.service';
		$this->commandManager->shouldReceive('send')->with($command, true)->andReturn($expected);
		Assert::same($expected, $this->managerSystemD->start());
	}

	/**
	 * Test function to start IQRF Gateway Daemon's service via supervisord in Docker container
	 */
	public function testStartDockerSupervisorD(): void {
		$expected = 'start';
		$command = 'supervisorctl start ' . $this->serviceName;
		$this->commandManager->shouldReceive('send')->with($command, true)->andReturn($expected);
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
		$command = 'systemctl stop ' . $this->serviceName . '.service';
		$this->commandManager->shouldReceive('send')->with($command, true)->andReturn($expected);
		Assert::same($expected, $this->managerSystemD->stop());
	}

	/**
	 * Test function to stop IQRF Gateway Daemon's service via supervisord in Docker container
	 */
	public function testStopDockerSupervisorD(): void {
		$expected = 'stop';
		$command = 'supervisorctl stop ' . $this->serviceName;
		$this->commandManager->shouldReceive('send')->with($command, true)->andReturn($expected);
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
		$command = 'systemctl restart ' . $this->serviceName . '.service';
		$this->commandManager->shouldReceive('send')->with($command, true)->andReturn($expected);
		Assert::same($expected, $this->managerSystemD->restart());
	}

	/**
	 * Test function to restart IQRF Gateway Daemon's service via supervisord in Docker container
	 */
	public function testRestartDockerSupervisorD(): void {
		$expected = 'restart';
		$command = 'supervisorctl restart ' . $this->serviceName;
		$this->commandManager->shouldReceive('send')->with($command, true)->andReturn($expected);
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
		$command = 'systemctl status ' . $this->serviceName . '.service';
		$this->commandManager->shouldReceive('send')->with($command, true)->andReturn($expected);
		Assert::same($expected, $this->managerSystemD->getStatus());
	}

	/**
	 * Test function to get status of IQRF Gateway Daemon's service via supervisord in Docker container
	 */
	public function testGetStatusDockerSupervisorD(): void {
		$expected = 'status';
		$command = 'supervisorctl status ' . $this->serviceName;
		$this->commandManager->shouldReceive('send')->with($command, true)->andReturn($expected);
		Assert::same($expected, $this->managerDocker->getStatus());
	}

	/**
	 * Test function to get status of IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testGetStatusUnknown(): void {
		Assert::exception([$this->managerUnknown, 'getStatus'], NotSupportedInitSystemException::class);
	}

	/**
	 * Set up the test environment
	 */
	protected function setUp(): void {
		$this->commandManager = Mockery::mock(CommandManager::class);
		$this->managerDocker = new ServiceManager('docker-supervisor', $this->commandManager);
		$this->managerSystemD = new ServiceManager('systemd', $this->commandManager);
		$this->managerUnknown = new ServiceManager('unknown', $this->commandManager);
	}

	/**
	 * Cleanup the test environment
	 */
	protected function tearDown(): void {
		Mockery::close();
	}

}

$test = new ServiceManagerTest();
$test->run();
