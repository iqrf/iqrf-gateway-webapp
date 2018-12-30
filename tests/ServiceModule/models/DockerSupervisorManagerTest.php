<?php

/**
 * TEST: App\ServiceModule\Models\DockerSupervisorManager
 * @covers App\ServiceModule\Models\DockerSupervisorManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\ServiceModule\Models;

use App\CoreModule\Models\CommandManager;
use App\ServiceModule\Models\DockerSupervisorManager;
use Mockery;
use Mockery\MockInterface;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for supervisord service manager in a Docker container
 */
class DockerSupervisorManagerTest extends TestCase {

	/**
	 * @var MockInterface Mocked command manager
	 */
	private $commandManager;

	/**
	 * @var DockerSupervisorManager Service manager for supervisord init daemon in a Docker container
	 */
	private $manager;

	/**
	 * @var string Name of service
	 */
	private $serviceName = 'iqrf-gateway-daemon';

	/**
	 * Test function to start IQRF Gateway Daemon's service via supervisord in Docker container
	 */
	public function testStartDockerSupervisorD(): void {
		$expected = 'start';
		$command = 'supervisorctl start ' . $this->serviceName;
		$this->commandManager->shouldReceive('send')->with($command, true)->andReturn($expected);
		Assert::same($expected, $this->manager->start());
	}

	/**
	 * Test function to stop IQRF Gateway Daemon's service via supervisord in Docker container
	 */
	public function testStop(): void {
		$expected = 'stop';
		$command = 'supervisorctl stop ' . $this->serviceName;
		$this->commandManager->shouldReceive('send')->with($command, true)->andReturn($expected);
		Assert::same($expected, $this->manager->stop());
	}

	/**
	 * Test function to restart IQRF Gateway Daemon's service via supervisord in Docker container
	 */
	public function testRestart(): void {
		$expected = 'restart';
		$command = 'supervisorctl restart ' . $this->serviceName;
		$this->commandManager->shouldReceive('send')->with($command, true)->andReturn($expected);
		Assert::same($expected, $this->manager->restart());
	}

	/**
	 * Test function to get status of IQRF Gateway Daemon's service via supervisord in Docker container
	 */
	public function testGetStatus(): void {
		$expected = 'status';
		$command = 'supervisorctl status ' . $this->serviceName;
		$this->commandManager->shouldReceive('send')->with($command, true)->andReturn($expected);
		Assert::same($expected, $this->manager->getStatus());
	}

	/**
	 * Set up the test environment
	 */
	protected function setUp(): void {
		$this->commandManager = Mockery::mock(CommandManager::class);
		$this->manager = new DockerSupervisorManager($this->commandManager);
	}

	/**
	 * Cleanup the test environment
	 */
	protected function tearDown(): void {
		Mockery::close();
	}

}

$test = new DockerSupervisorManagerTest();
$test->run();
