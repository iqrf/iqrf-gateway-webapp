<?php

/**
 * TEST: App\ServiceModule\Models\SupervisordManager
 * @covers App\ServiceModule\Models\SupervisordManager
 * @phpVersion >= 7.2
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\ServiceModule\Models;

use App\ServiceModule\Exceptions\NotImplementedException;
use App\ServiceModule\Models\SupervisordManager;
use Tester\Assert;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for supervisord service manager in a Docker container
 */
final class SupervisordManagerTest extends CommandTestCase {

	/**
	 * @var SupervisordManager Service manager for supervisord init daemon in a Docker container
	 */
	private $manager;

	/**
	 * Name of service
	 */
	private const SERVICE_NAME = 'iqrf-gateway-daemon';

	/**
	 * Tests the function to disable IQRF Gateway Daemon's service via supervisord
	 */
	public function testDisable(): void {
		Assert::exception([$this->manager, 'disable'], NotImplementedException::class);
	}

	/**
	 * Tests the function to enable IQRF Gateway Daemon's service via supervisord
	 */
	public function testEnable(): void {
		Assert::exception([$this->manager, 'enable'], NotImplementedException::class);
	}

	/**
	 * Tests the function to check IQRF Gateway Daemon's service is active via supervisord
	 */
	public function testIsActive(): void {
		Assert::exception([$this->manager, 'isActive'], NotImplementedException::class);
	}

	/**
	 * Tests the function to check IQRF Gateway Daemon's service is enabled via supervisord
	 */
	public function testIsEnabled(): void {
		Assert::exception([$this->manager, 'isEnabled'], NotImplementedException::class);
	}

	/**
	 * Tests the function to start the service via supervisord
	 */
	public function testStart(): void {
		$command = 'supervisorctl start ' . self::SERVICE_NAME;
		$this->receiveCommand($command, true);
		Assert::noError([$this->manager, 'start']);
	}

	/**
	 * Tests the function to stop the service via supervisord
	 */
	public function testStop(): void {
		$command = 'supervisorctl stop ' . self::SERVICE_NAME;
		$this->receiveCommand($command, true);
		Assert::noError([$this->manager, 'stop']);
	}

	/**
	 * Tests the function to restart the service via supervisord
	 */
	public function testRestart(): void {
		$command = 'supervisorctl restart ' . self::SERVICE_NAME;
		$this->receiveCommand($command, true);
		Assert::noError([$this->manager, 'restart']);
	}

	/**
	 * Tests the function to get status of the service via supervisord
	 */
	public function testGetStatus(): void {
		$expected = 'status';
		$command = 'supervisorctl status ' . self::SERVICE_NAME;
		$this->receiveCommand($command, true, $expected);
		Assert::same($expected, $this->manager->getStatus());
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new SupervisordManager($this->commandManager, self::SERVICE_NAME);
	}

}

$test = new SupervisordManagerTest();
$test->run();
