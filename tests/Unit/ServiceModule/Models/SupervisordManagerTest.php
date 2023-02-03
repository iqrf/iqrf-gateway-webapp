<?php

/**
 * TEST: App\ServiceModule\Models\SupervisordManager
 * @covers App\ServiceModule\Models\SupervisordManager
 * @phpVersion >= 7.4
 * @testCase
 */
/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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
	private SupervisordManager $manager;

	/**
	 * @var string Name of service
	 */
	private const SERVICE_NAME = 'iqrf-gateway-daemon';

	/**
	 * Tests the function to disable IQRF Gateway Daemon's service via supervisord
	 */
	public function testDisable(): void {
		Assert::exception(function (): void {
			$this->manager->disable(self::SERVICE_NAME);
		}, NotImplementedException::class);
	}

	/**
	 * Tests the function to enable IQRF Gateway Daemon's service via supervisord
	 */
	public function testEnable(): void {
		Assert::exception(function (): void {
			$this->manager->enable(self::SERVICE_NAME);
		}, NotImplementedException::class);
	}

	/**
	 * Tests the function to check IQRF Gateway Daemon's service is active via supervisord
	 */
	public function testIsActive(): void {
		Assert::exception(function (): void {
			$this->manager->isActive(self::SERVICE_NAME);
		}, NotImplementedException::class);
	}

	/**
	 * Tests the function to check IQRF Gateway Daemon's service is enabled via supervisord
	 */
	public function testIsEnabled(): void {
		Assert::exception(function (): void {
			$this->manager->isEnabled(self::SERVICE_NAME);
		}, NotImplementedException::class);
	}

	/**
	 * Tests the function to start the service via supervisord
	 */
	public function testStart(): void {
		$command = 'supervisorctl start \'' . self::SERVICE_NAME . '\'';
		$this->receiveCommand($command, true);
		Assert::noError(function (): void {
			$this->manager->start(self::SERVICE_NAME);
		});
	}

	/**
	 * Tests the function to stop the service via supervisord
	 */
	public function testStop(): void {
		$command = 'supervisorctl stop \'' . self::SERVICE_NAME . '\'';
		$this->receiveCommand($command, true);
		Assert::noError(function (): void {
			$this->manager->stop(self::SERVICE_NAME);
		});
	}

	/**
	 * Tests the function to restart the service via supervisord
	 */
	public function testRestart(): void {
		$command = 'supervisorctl restart \'' . self::SERVICE_NAME . '\'';
		$this->receiveCommand($command, true);
		Assert::noError(function (): void {
			$this->manager->restart(self::SERVICE_NAME);
		});
	}

	/**
	 * Tests the function to get status of the service via supervisord
	 */
	public function testGetStatus(): void {
		$expected = 'status';
		$command = 'supervisorctl status \'' . self::SERVICE_NAME . '\'';
		$this->receiveCommand($command, true, $expected);
		Assert::same($expected, $this->manager->getStatus(self::SERVICE_NAME));
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new SupervisordManager($this->commandManager);
	}

}

$test = new SupervisordManagerTest();
$test->run();
