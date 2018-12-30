<?php

/**
 * TEST: App\ServiceModule\Models\SystemDManager
 * @covers App\ServiceModule\Models\SystemDManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\ServiceModule\Models;

use App\CoreModule\Models\CommandManager;
use App\ServiceModule\Models\SystemDManager;
use Mockery;
use Mockery\MockInterface;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for systemD service manager
 */
class SystemDManagerTest extends TestCase {

	/**
	 * @var MockInterface Mocked command manager
	 */
	private $commandManager;

	/**
	 * @var SystemDManager Service manager for systemD init daemon
	 */
	private $manager;

	/**
	 * @var string Name of service
	 */
	private $serviceName = 'iqrf-gateway-daemon';

	/**
	 * Test function to start IQRF Gateway Daemon's service via systemD
	 */
	public function testStart(): void {
		$expected = 'start';
		$command = 'systemctl start ' . $this->serviceName . '.service';
		$this->commandManager->shouldReceive('send')->with($command, true)->andReturn($expected);
		Assert::same($expected, $this->manager->start());
	}

	/**
	 * Test function to stop IQRF Gateway Daemon's service via systemD
	 */
	public function testStop(): void {
		$expected = 'stop';
		$command = 'systemctl stop ' . $this->serviceName . '.service';
		$this->commandManager->shouldReceive('send')->with($command, true)->andReturn($expected);
		Assert::same($expected, $this->manager->stop());
	}

	/**
	 * Test function to restart IQRF Gateway Daemon's service via systemD
	 */
	public function testRestart(): void {
		$expected = 'restart';
		$command = 'systemctl restart ' . $this->serviceName . '.service';
		$this->commandManager->shouldReceive('send')->with($command, true)->andReturn($expected);
		Assert::same($expected, $this->manager->restart());
	}

	/**
	 * Test function to get status of IQRF Gateway Daemon's service via systemD
	 */
	public function testGetStatus(): void {
		$expected = 'status';
		$command = 'systemctl status ' . $this->serviceName . '.service';
		$this->commandManager->shouldReceive('send')->with($command, true)->andReturn($expected);
		Assert::same($expected, $this->manager->getStatus());
	}

	/**
	 * Set up the test environment
	 */
	protected function setUp(): void {
		$this->commandManager = Mockery::mock(CommandManager::class);
		$this->manager = new SystemDManager($this->commandManager);
	}

	/**
	 * Cleanup the test environment
	 */
	protected function tearDown(): void {
		Mockery::close();
	}

}

$test = new SystemDManagerTest();
$test->run();
