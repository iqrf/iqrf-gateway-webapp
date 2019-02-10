<?php

/**
 * TEST: App\ServiceModule\Models\UnknownManager
 * @covers App\ServiceModule\Models\UnknownManager
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\ServiceModule\Models;

use App\ServiceModule\Exceptions\NotSupportedInitSystemException;
use App\ServiceModule\Models\UnknownManager;
use Tester\Assert;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for service manager
 */
class UnknownManagerTest extends CommandTestCase {

	/**
	 * @var UnknownManager Service manager for unknown init daemon
	 */
	private $manager;

	/**
	 * Tests the function to start IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testStart(): void {
		Assert::exception([$this->manager, 'start'], NotSupportedInitSystemException::class);
	}

	/**
	 * Tests the function to stop IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testStop(): void {
		Assert::exception([$this->manager, 'stop'], NotSupportedInitSystemException::class);
	}

	/**
	 * Tests the function to restart IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testRestart(): void {
		Assert::exception([$this->manager, 'restart'], NotSupportedInitSystemException::class);
	}

	/**
	 * Tests the function to get status of IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testGetStatus(): void {
		Assert::exception([$this->manager, 'getStatus'], NotSupportedInitSystemException::class);
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new UnknownManager($this->commandManager);
	}

}

$test = new UnknownManagerTest();
$test->run();
