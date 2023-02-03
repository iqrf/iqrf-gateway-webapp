<?php

/**
 * TEST: App\ServiceModule\Models\ServiceManager
 * @covers App\ServiceModule\Models\ServiceManager
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

namespace Tests\Integration\ServiceModule\Models;

use App\ServiceModule\Exceptions\UnsupportedInitSystemException;
use App\ServiceModule\Models\ServiceManager;
use Tester\Assert;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for service manager
 */
final class ServiceManagerTest extends CommandTestCase {

	/**
	 * @var ServiceManager Service manager for systemD init daemon
	 */
	private ServiceManager $managerSystemD;

	/**
	 * @var ServiceManager Service manager for unknown init daemon
	 */
	private ServiceManager $managerUnknown;

	/**
	 * @var string Name of service
	 */
	private const SERVICE_NAME = 'iqrf-gateway-daemon';

	/**
	 * Tests the function to disable the service via systemD
	 */
	public function testDisableSystemD(): void {
		$commands = [
			'systemctl disable \'' . self::SERVICE_NAME . '.service\'',
			'systemctl stop \'' . self::SERVICE_NAME . '.service\'',
		];
		foreach ($commands as $command) {
			$this->receiveCommand($command, true);
		}
		Assert::noError(function (): void {
			$this->managerSystemD->disable(self::SERVICE_NAME);
		});
	}

	/**
	 * Tests the function to disable the service via unknown init daemon
	 */
	public function testDisableUnknown(): void {
		Assert::exception(function (): void {
			$this->managerUnknown->disable(self::SERVICE_NAME);
		}, UnsupportedInitSystemException::class);
	}

	/**
	 * Tests the function to enable the service via systemD
	 */
	public function testEnableSystemD(): void {
		$commands = [
			'systemctl enable \'' . self::SERVICE_NAME . '.service\'',
			'systemctl start \'' . self::SERVICE_NAME . '.service\'',
		];
		foreach ($commands as $command) {
			$this->receiveCommand($command, true);
		}
		Assert::noError(function (): void {
			$this->managerSystemD->enable(self::SERVICE_NAME);
		});
	}

	/**
	 * Tests the function to enable the service via unknown init daemon
	 */
	public function testEnableUnknown(): void {
		Assert::exception(function (): void {
			$this->managerUnknown->enable(self::SERVICE_NAME);
		}, UnsupportedInitSystemException::class);
	}

	/**
	 * Tests the function to check if the service is active via systemD
	 */
	public function testIsActiveSystemD(): void {
		$command = 'systemctl is-active \'' . self::SERVICE_NAME . '.service\'';
		$this->receiveCommand($command, true, 'active');
		Assert::true($this->managerSystemD->isActive(self::SERVICE_NAME));
	}

	/**
	 * Tests the function to check if the service is active via unknown init daemon
	 */
	public function testIsActiveUnknown(): void {
		Assert::exception(function (): void {
			$this->managerUnknown->isActive(self::SERVICE_NAME);
		}, UnsupportedInitSystemException::class);
	}

	/**
	 * Tests the function to check if the service is enabled via systemD
	 */
	public function testIsEnabledSystemD(): void {
		$command = 'systemctl is-enabled \'' . self::SERVICE_NAME . '.service\'';
		$this->receiveCommand($command, true, 'enabled');
		Assert::true($this->managerSystemD->isEnabled(self::SERVICE_NAME));
	}

	/**
	 * Tests the function to check if the service is enabled via unknown init daemon
	 */
	public function testIsEnabledUnknown(): void {
		Assert::exception(function (): void {
			$this->managerUnknown->isEnabled(self::SERVICE_NAME);
		}, UnsupportedInitSystemException::class);
	}

	/**
	 * Tests the function to start the service via systemD
	 */
	public function testStartSystemD(): void {
		$command = 'systemctl start \'' . self::SERVICE_NAME . '.service\'';
		$this->receiveCommand($command, true);
		Assert::noError(function (): void {
			$this->managerSystemD->start(self::SERVICE_NAME);
		});
	}

	/**
	 * Tests the function to start the service via unknown init daemon
	 */
	public function testStartUnknown(): void {
		Assert::exception(function (): void {
			$this->managerUnknown->start(self::SERVICE_NAME);
		}, UnsupportedInitSystemException::class);
	}

	/**
	 * Tests the function to stop the service via systemD
	 */
	public function testStopSystemD(): void {
		$command = 'systemctl stop \'' . self::SERVICE_NAME . '.service\'';
		$this->receiveCommand($command, true);
		Assert::noError(function (): void {
			$this->managerSystemD->stop(self::SERVICE_NAME);
		});
	}

	/**
	 * Tests the function to stop the service via unknown init daemon
	 */
	public function testStopUnknown(): void {
		Assert::exception(function (): void {
			$this->managerUnknown->stop(self::SERVICE_NAME);
		}, UnsupportedInitSystemException::class);
	}

	/**
	 * Tests the function to restart the service via systemD
	 */
	public function testRestartSystemD(): void {
		$command = 'systemctl restart \'' . self::SERVICE_NAME . '.service\'';
		$this->receiveCommand($command, true);
		Assert::noError(function (): void {
			$this->managerSystemD->restart(self::SERVICE_NAME);
		});
	}

	/**
	 * Tests the function to restart the service via unknown init daemon
	 */
	public function testRestartUnknown(): void {
		Assert::exception(function (): void {
			$this->managerUnknown->restart(self::SERVICE_NAME);
		}, UnsupportedInitSystemException::class);
	}

	/**
	 * Tests the function to get status of the service via systemD
	 */
	public function testGetStatusSystemD(): void {
		$expected = 'status';
		$command = 'systemctl status \'' . self::SERVICE_NAME . '.service\'';
		$this->receiveCommand($command, true, $expected);
		Assert::same($expected, $this->managerSystemD->getStatus(self::SERVICE_NAME));
	}

	/**
	 * Tests the function to get status of the service via unknown init daemon
	 */
	public function testGetStatusUnknown(): void {
		Assert::exception(function (): void {
			$this->managerUnknown->getStatus(self::SERVICE_NAME);
		}, UnsupportedInitSystemException::class);
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
