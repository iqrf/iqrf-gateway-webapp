<?php

/**
 * TEST: App\ServiceModule\Models\UnknownManager
 * @covers App\ServiceModule\Models\UnknownManager
 * @phpVersion >= 7.2
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\ServiceModule\Models;

use App\ServiceModule\Exceptions\UnsupportedInitSystemException;
use App\ServiceModule\Models\UnknownManager;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for service manager
 */
final class UnknownManagerTest extends TestCase {

	/**
	 * @var UnknownManager Service manager for unknown init daemon
	 */
	private $manager;

	/**
	 * Tests the function to disable IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testDisable(): void {
		Assert::exception(function (): void {
			$this->manager->disable();
		}, UnsupportedInitSystemException::class);
	}

	/**
	 * Tests the function to enable IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testEnable(): void {
		Assert::exception(function (): void {
			$this->manager->enable();
		}, UnsupportedInitSystemException::class);
	}

	/**
	 * Tests the function to check IQRF Gateway Daemon's service is active via unknown init daemon
	 */
	public function testIsActive(): void {
		Assert::exception(function (): void {
			$this->manager->isActive();
		}, UnsupportedInitSystemException::class);
	}

	/**
	 * Tests the function to check IQRF Gateway Daemon's service is enabled via unknown init daemon
	 */
	public function testIsEnabled(): void {
		Assert::exception(function (): void {
			$this->manager->isEnabled();
		}, UnsupportedInitSystemException::class);
	}

	/**
	 * Tests the function to start IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testStart(): void {
		Assert::exception(function (): void {
			$this->manager->start();
		}, UnsupportedInitSystemException::class);
	}

	/**
	 * Tests the function to stop IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testStop(): void {
		Assert::exception(function (): void {
			$this->manager->stop();
		}, UnsupportedInitSystemException::class);
	}

	/**
	 * Tests the function to restart IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testRestart(): void {
		Assert::exception(function (): void {
			$this->manager->restart();
		}, UnsupportedInitSystemException::class);
	}

	/**
	 * Tests the function to get status of IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testGetStatus(): void {
		Assert::exception(function (): void {
			$this->manager->getStatus();
		}, UnsupportedInitSystemException::class);
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new UnknownManager();
	}

}

$test = new UnknownManagerTest();
$test->run();
