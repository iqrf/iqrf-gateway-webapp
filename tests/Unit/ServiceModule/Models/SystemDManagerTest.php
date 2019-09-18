<?php

/**
 * TEST: App\ServiceModule\Models\SystemDManager
 * @covers App\ServiceModule\Models\SystemDManager
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\ServiceModule\Models;

use App\ServiceModule\Enums\ServiceStates;
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
	private $serviceName = 'iqrf-gateway-daemon.service';

	/**
	 * Tests the function to disable the service via systemD
	 */
	public function testDisable(): void {
		$commands = [
			'systemctl stop ' . $this->serviceName,
			'systemctl disable ' . $this->serviceName,
		];
		foreach ($commands as $command) {
			$this->receiveCommand($command, true);
		}
		Assert::noError([$this->manager, 'disable']);
	}

	/**
	 * Tests the function to enable the service via systemD
	 */
	public function testEnable(): void {
		$commands = [
			'systemctl enable ' . $this->serviceName,
			'systemctl start ' . $this->serviceName,
		];
		foreach ($commands as $command) {
			$this->receiveCommand($command, true);
		}
		Assert::noError([$this->manager, 'enable']);
	}

	/**
	 * Tests the function to check if the service is enabled via systemD
	 */
	public function testIsEnabled(): void {
		$expected = ServiceStates::ENABLED();
		$command = 'systemctl is-enabled ' . $this->serviceName;
		$this->receiveCommand($command, true, 'enabled');
		Assert::same($expected, $this->manager->isEnabled());
	}

	/**
	 * Tests the function to start the service via systemD
	 */
	public function testStart(): void {
		$command = 'systemctl start ' . $this->serviceName;
		$this->receiveCommand($command, true);
		Assert::noError([$this->manager, 'start']);
	}

	/**
	 * Tests the function to stop the service via systemD
	 */
	public function testStop(): void {
		$command = 'systemctl stop ' . $this->serviceName;
		$this->receiveCommand($command, true);
		Assert::noError([$this->manager, 'stop']);
	}

	/**
	 * Tests the function to restart the service via systemD
	 */
	public function testRestart(): void {
		$command = 'systemctl restart ' . $this->serviceName;
		$this->receiveCommand($command, true);
		Assert::noError([$this->manager, 'restart']);
	}

	/**
	 * Tests the function to get status of the service via systemD
	 */
	public function testGetStatus(): void {
		$expected = 'status';
		$command = 'systemctl status ' . $this->serviceName;
		$this->receiveCommand($command, true, $expected);
		Assert::same($expected, $this->manager->getStatus());
	}

	/**
	 * Set up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new SystemDManager($this->commandManager, 'iqrf-gateway-daemon');
	}

}

$test = new SystemDManagerTest();
$test->run();
