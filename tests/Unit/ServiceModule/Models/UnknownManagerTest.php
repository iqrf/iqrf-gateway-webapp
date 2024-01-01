<?php

/**
 * TEST: App\ServiceModule\Models\UnknownManager
 * @covers App\ServiceModule\Models\UnknownManager
 * @phpVersion >= 7.4
 * @testCase
 */
/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
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
	 * Name of service
	 */
	private const SERVICE_NAME = 'iqrf-gateway-daemon';

	/**
	 * @var UnknownManager Service manager for unknown init daemon
	 */
	private UnknownManager $manager;

	/**
	 * Tests the function to disable IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testDisable(): void {
		Assert::exception(function (): void {
			$this->manager->disable(self::SERVICE_NAME);
		}, UnsupportedInitSystemException::class);
	}

	/**
	 * Tests the function to enable IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testEnable(): void {
		Assert::exception(function (): void {
			$this->manager->enable(self::SERVICE_NAME);
		}, UnsupportedInitSystemException::class);
	}

	/**
	 * Tests the function to check IQRF Gateway Daemon's service is active via unknown init daemon
	 */
	public function testIsActive(): void {
		Assert::exception(function (): void {
			$this->manager->isActive(self::SERVICE_NAME);
		}, UnsupportedInitSystemException::class);
	}

	/**
	 * Tests the function to check IQRF Gateway Daemon's service is enabled via unknown init daemon
	 */
	public function testIsEnabled(): void {
		Assert::exception(function (): void {
			$this->manager->isEnabled(self::SERVICE_NAME);
		}, UnsupportedInitSystemException::class);
	}

	/**
	 * Tests the function to start IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testStart(): void {
		Assert::exception(function (): void {
			$this->manager->start(self::SERVICE_NAME);
		}, UnsupportedInitSystemException::class);
	}

	/**
	 * Tests the function to stop IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testStop(): void {
		Assert::exception(function (): void {
			$this->manager->stop(self::SERVICE_NAME);
		}, UnsupportedInitSystemException::class);
	}

	/**
	 * Tests the function to restart IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testRestart(): void {
		Assert::exception(function (): void {
			$this->manager->restart(self::SERVICE_NAME);
		}, UnsupportedInitSystemException::class);
	}

	/**
	 * Tests the function to get status of IQRF Gateway Daemon's service via unknown init daemon
	 */
	public function testGetStatus(): void {
		Assert::exception(function (): void {
			$this->manager->getStatus(self::SERVICE_NAME);
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
