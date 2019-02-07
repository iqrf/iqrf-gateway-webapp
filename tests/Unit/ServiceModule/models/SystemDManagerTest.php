<?php

/**
 * TEST: App\ServiceModule\Models\SystemDManager
 * @covers App\ServiceModule\Models\SystemDManager
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Test\Unit\ServiceModule\Models;

use App\ServiceModule\Models\SystemDManager;
use Tester\Assert;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for systemD service manager
 */
class SystemDManagerTest extends CommandTestCase {

	/**
	 * @var SystemDManager Service manager for systemD init daemon
	 */
	private $manager;

	/**
	 * @var string Name of service
	 */
	private $serviceName = 'iqrf-gateway-daemon';

	/**
	 * Tests the function to start IQRF Gateway Daemon's service via systemD
	 */
	public function testStart(): void {
		$expected = 'start';
		$command = 'systemctl start ' . $this->serviceName . '.service';
		$this->receiveCommand($command, true, $expected);
		Assert::same($expected, $this->manager->start());
	}

	/**
	 * Tests the function to stop IQRF Gateway Daemon's service via systemD
	 */
	public function testStop(): void {
		$expected = 'stop';
		$command = 'systemctl stop ' . $this->serviceName . '.service';
		$this->receiveCommand($command, true, $expected);
		Assert::same($expected, $this->manager->stop());
	}

	/**
	 * Tests the function to restart IQRF Gateway Daemon's service via systemD
	 */
	public function testRestart(): void {
		$expected = 'restart';
		$command = 'systemctl restart ' . $this->serviceName . '.service';
		$this->receiveCommand($command, true, $expected);
		Assert::same($expected, $this->manager->restart());
	}

	/**
	 * Tests the function to get status of IQRF Gateway Daemon's service via systemD
	 */
	public function testGetStatus(): void {
		$expected = 'status';
		$command = 'systemctl status ' . $this->serviceName . '.service';
		$this->receiveCommand($command, true, $expected);
		Assert::same($expected, $this->manager->getStatus());
	}

	/**
	 * Set up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new SystemDManager($this->commandManager);
	}

}

$test = new SystemDManagerTest();
$test->run();
