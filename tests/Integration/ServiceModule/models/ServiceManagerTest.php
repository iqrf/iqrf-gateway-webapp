<?php

/**
 * TEST: App\ServiceModule\Models\ServiceManager
 * @covers App\ServiceModule\Models\ServiceManager
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Test\Integration\ServiceModule\Models;

use App\ServiceModule\Exceptions\NotSupportedInitSystemException;
use App\ServiceModule\Models\ServiceManager;
use Tester\Assert;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for service manager
 */
class ServiceManagerTest extends CommandTestCase {

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
	 * Tests the function to start IQRF Gateway Daemon's service via systemD
	 */
	public function testStartSystemD(): void {
		$expected = 'start';
		$command = 'systemctl start ' . $this->serviceName . '.service';
		$this->receiveCommand($command, true, $expected);
		Assert::same($expected, $this->managerSystemD->start());
	}

	/**
	 * Tests the function to start IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testStartUnknown(): void {
		Assert::exception([$this->managerUnknown, 'start'], NotSupportedInitSystemException::class);
	}

	/**
	 * Tests the function to stop IQRF Gateway Daemon's service via systemD
	 */
	public function testStopSystemD(): void {
		$expected = 'stop';
		$command = 'systemctl stop ' . $this->serviceName . '.service';
		$this->receiveCommand($command, true, $expected);
		Assert::same($expected, $this->managerSystemD->stop());
	}

	/**
	 * Tests the function to stop IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testStopUnknown(): void {
		Assert::exception([$this->managerUnknown, 'stop'], NotSupportedInitSystemException::class);
	}

	/**
	 * Tests the function to restart IQRF Gateway Daemon's service via systemD
	 */
	public function testRestartSystemD(): void {
		$expected = 'restart';
		$command = 'systemctl restart ' . $this->serviceName . '.service';
		$this->receiveCommand($command, true, $expected);
		Assert::same($expected, $this->managerSystemD->restart());
	}

	/**
	 * Tests the function to restart IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testRestartUnknown(): void {
		Assert::exception([$this->managerUnknown, 'restart'], NotSupportedInitSystemException::class);
	}

	/**
	 * Tests the function to get status of IQRF Gateway Daemon's service via systemD
	 */
	public function testGetStatusSystemD(): void {
		$expected = 'status';
		$command = 'systemctl status ' . $this->serviceName . '.service';
		$this->receiveCommand($command, true, $expected);
		Assert::same($expected, $this->managerSystemD->getStatus());
	}

	/**
	 * Tests the function to get status of IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testGetStatusUnknown(): void {
		Assert::exception([$this->managerUnknown, 'getStatus'], NotSupportedInitSystemException::class);
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->managerSystemD = new ServiceManager('systemd', $this->commandManager);
		$this->managerUnknown = new ServiceManager('unknown', $this->commandManager);
	}

}

$test = new ServiceManagerTest();
$test->run();
