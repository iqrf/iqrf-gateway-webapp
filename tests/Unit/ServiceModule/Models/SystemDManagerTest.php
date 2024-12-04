<?php

/**
 * TEST: App\ServiceModule\Models\SystemDManager
 * @covers App\ServiceModule\Models\SystemDManager
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

use App\ServiceModule\Exceptions\NonexistentServiceException;
use App\ServiceModule\Models\SystemDManager;
use Iqrf\CommandExecutor\Tester\Traits\CommandExecutorTestCase;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for systemD service manager
 */
final class SystemDManagerTest extends TestCase {

	use CommandExecutorTestCase;

	/**
	 * IQRF Gateway Daemon service name
	 */
	private const DAEMON_SERVICE_NAME = 'iqrf-gateway-daemon';

	/**
	 * IQRF Gateway Controller service name
	 */
	private const CONTROLLER_SERVICE_NAME = 'iqrf-gateway-controller';

	/**
	 * Unknown service name
	 */
	private const UNKNOWN_SERVICE_NAME = 'unknown';

	/**
	 * @var SystemDManager Service manager for systemD init daemon
	 */
	private SystemDManager $manager;

	/**
	 * Tests the function to disable the service via systemD
	 */
	public function testDisable(): void {
		$commands = [
			'systemctl disable --now \'' . self::DAEMON_SERVICE_NAME . '.service\'',
			'systemctl disable \'' . self::DAEMON_SERVICE_NAME . '.service\'',
			'systemctl disable --now \'' . self::DAEMON_SERVICE_NAME . '.service\' \'' . self::CONTROLLER_SERVICE_NAME . '.service\'',
			'systemctl disable \'' . self::DAEMON_SERVICE_NAME . '.service\' \'' . self::CONTROLLER_SERVICE_NAME . '.service\'',
		];
		foreach ($commands as $command) {
			$this->receiveCommand(command: $command, needSudo: true);
		}
		Assert::noError(function (): void {
			$this->manager->disable(self::DAEMON_SERVICE_NAME);
			$this->manager->disable(self::DAEMON_SERVICE_NAME, false);
			$this->manager->disable([self::DAEMON_SERVICE_NAME, self::CONTROLLER_SERVICE_NAME]);
			$this->manager->disable([self::DAEMON_SERVICE_NAME, self::CONTROLLER_SERVICE_NAME], false);
		});
	}

	/**
	 * Tests the function to disable the service via systemD - unknown service
	 */
	public function testDisableUnknown(): void {
		$command = 'systemctl disable \'' . self::UNKNOWN_SERVICE_NAME . '.service\'';
		$stderr = 'Failed to disable unit: Unit file unknown.service does not exist.';
		$this->receiveCommand(
			command: $command,
			needSudo: true,
			stderr: $stderr,
			exitCode: 1,
		);
		Assert::throws(function (): void {
			$this->manager->disable(self::UNKNOWN_SERVICE_NAME, false);
		}, NonexistentServiceException::class, $stderr);
	}

	/**
	 * Tests the function to enable the service via systemD
	 */
	public function testEnable(): void {
		$commands = [
			'systemctl enable --now \'' . self::DAEMON_SERVICE_NAME . '.service\'',
			'systemctl enable \'' . self::DAEMON_SERVICE_NAME . '.service\'',
			'systemctl enable --now \'' . self::DAEMON_SERVICE_NAME . '.service\' \'' . self::CONTROLLER_SERVICE_NAME . '.service\'',
			'systemctl enable \'' . self::DAEMON_SERVICE_NAME . '.service\' \'' . self::CONTROLLER_SERVICE_NAME . '.service\'',
		];
		foreach ($commands as $command) {
			$this->receiveCommand(command: $command, needSudo: true);
		}
		Assert::noError(function (): void {
			$this->manager->enable(self::DAEMON_SERVICE_NAME);
			$this->manager->enable(self::DAEMON_SERVICE_NAME, false);
			$this->manager->enable([self::DAEMON_SERVICE_NAME, self::CONTROLLER_SERVICE_NAME]);
			$this->manager->enable([self::DAEMON_SERVICE_NAME, self::CONTROLLER_SERVICE_NAME], false);
		});
	}

	/**
	 * Tests the function to enable the service via systemD - unknown service
	 */
	public function testEnableUnknown(): void {
		$command = 'systemctl enable \'' . self::UNKNOWN_SERVICE_NAME . '.service\'';
		$stderr = 'Failed to enable unit: Unit file unknown.service does not exist.';
		$this->receiveCommand(
			command: $command,
			needSudo: true,
			stderr: $stderr,
			exitCode: 1,
		);
		Assert::throws(function (): void {
			$this->manager->enable(self::UNKNOWN_SERVICE_NAME, false);
		}, NonexistentServiceException::class, $stderr);
	}

	/**
	 * Tests the function to check if the service is active via systemD
	 */
	public function testIsActive(): void {
		$command = 'systemctl is-active \'' . self::DAEMON_SERVICE_NAME . '.service\'';
		$this->receiveCommand(
			command: $command,
			needSudo: true,
			stdout: 'active',
		);
		Assert::true($this->manager->isActive(self::DAEMON_SERVICE_NAME));
	}

	/**
	 * Tests the function to check if the service is active via systemD - unknown service
	 */
	public function testIsActiveUnknown(): void {
		$command = 'systemctl is-active \'' . self::UNKNOWN_SERVICE_NAME . '.service\'';
		$this->receiveCommand(
			command: $command,
			needSudo: true,
			stdout: 'inactive',
		);
		Assert::false($this->manager->isActive(self::UNKNOWN_SERVICE_NAME));
	}

	/**
	 * Tests the function to check if the service is enabled via systemD
	 */
	public function testIsEnabled(): void {
		$command = 'systemctl is-enabled \'' . self::DAEMON_SERVICE_NAME . '.service\'';
		$this->receiveCommand(
			command: $command,
			needSudo: true,
			stdout: 'enabled',
		);
		Assert::true($this->manager->isEnabled(self::DAEMON_SERVICE_NAME));
	}

	/**
	 * Tests the function to check if the service is enabled via systemD - unknown service
	 */
	public function testIsEnabledUnknown(): void {
		$command = 'systemctl is-enabled \'' . self::UNKNOWN_SERVICE_NAME . '.service\'';
		$stderr = 'Failed to get unit file state for unknown.service: No such file or directory';
		$this->receiveCommand(
			command: $command,
			needSudo: true,
			stderr: $stderr,
			exitCode: 1,
		);
		Assert::throws(function (): void {
			$this->manager->isEnabled(self::UNKNOWN_SERVICE_NAME);
		}, NonexistentServiceException::class, $stderr);
	}

	/**
	 * Tests the function to start the service via systemD
	 */
	public function testStart(): void {
		$commands = [
			'systemctl start \'' . self::DAEMON_SERVICE_NAME . '.service\'',
			'systemctl start \'' . self::DAEMON_SERVICE_NAME . '.service\' \'' . self::CONTROLLER_SERVICE_NAME . '.service\'',
		];
		foreach ($commands as $command) {
			$this->receiveCommand(command: $command, needSudo: true);
		}
		Assert::noError(function (): void {
			$this->manager->start(self::DAEMON_SERVICE_NAME);
			$this->manager->start([self::DAEMON_SERVICE_NAME, self::CONTROLLER_SERVICE_NAME]);
		});
	}

	/**
	 * Tests the function to start the service via systemD - unknown service
	 */
	public function testStartUnknown(): void {
		$command = 'systemctl start \'' . self::UNKNOWN_SERVICE_NAME . '.service\'';
		$stderr = 'Failed to start unknown.service: Unit unknown.service not found.';
		$this->receiveCommand(
			command: $command,
			needSudo: true,
			stderr: $stderr,
			exitCode: 5,
		);
		Assert::throws(function (): void {
			$this->manager->start(self::UNKNOWN_SERVICE_NAME);
		}, NonexistentServiceException::class, $stderr);
	}

	/**
	 * Tests the function to stop the service via systemD
	 */
	public function testStop(): void {
		$commands = [
			'systemctl stop \'' . self::DAEMON_SERVICE_NAME . '.service\'',
			'systemctl stop \'' . self::DAEMON_SERVICE_NAME . '.service\' \'' . self::CONTROLLER_SERVICE_NAME . '.service\'',
		];
		foreach ($commands as $command) {
			$this->receiveCommand(command: $command, needSudo: true);
		}
		Assert::noError(function (): void {
			$this->manager->stop(self::DAEMON_SERVICE_NAME);
			$this->manager->stop([self::DAEMON_SERVICE_NAME, self::CONTROLLER_SERVICE_NAME]);
		});
	}

	/**
	 * Tests the function to stop the service via systemD - unknown service
	 */
	public function testStopUnknown(): void {
		$command = 'systemctl stop \'' . self::UNKNOWN_SERVICE_NAME . '.service\'';
		$stderr = 'Failed to stop unknown.service: Unit unknown.service not found.';
		$this->receiveCommand(
			command: $command,
			needSudo: true,
			stderr: $stderr,
			exitCode: 5,
		);
		Assert::throws(function (): void {
			$this->manager->stop(self::UNKNOWN_SERVICE_NAME);
		}, NonexistentServiceException::class, $stderr);
	}

	/**
	 * Tests the function to restart the service via systemD
	 */
	public function testRestart(): void {
		$command = 'systemctl restart \'' . self::DAEMON_SERVICE_NAME . '.service\'';
		$this->receiveCommand(command: $command, needSudo: true);
		Assert::noError(function (): void {
			$this->manager->restart(self::DAEMON_SERVICE_NAME);
		});
	}

	/**
	 * Tests the function to restart the service via systemD - unknown service
	 */
	public function testRestartUnknown(): void {
		$command = 'systemctl restart \'' . self::UNKNOWN_SERVICE_NAME . '.service\'';
		$stderr = 'Failed to restart unknown.service: Unit unknown.service not found.';
		$this->receiveCommand(
			command: $command,
			needSudo: true,
			stderr: $stderr,
			exitCode: 5,
		);
		Assert::throws(function (): void {
			$this->manager->restart(self::UNKNOWN_SERVICE_NAME);
		}, NonexistentServiceException::class, $stderr);
	}

	/**
	 * Tests the function to get status of the service via systemD
	 */
	public function testGetStatus(): void {
		$expected = 'status';
		$command = 'systemctl status \'' . self::DAEMON_SERVICE_NAME . '.service\'';
		$this->receiveCommand(
			command: $command,
			needSudo: true,
			stdout: $expected,
		);
		Assert::same($expected, $this->manager->getStatus(self::DAEMON_SERVICE_NAME));
	}

	/**
	 * Tests the function to get status of the service via systemD - unknown service
	 */
	public function testGetStatusUnknown(): void {
		$command = 'systemctl status \'' . self::UNKNOWN_SERVICE_NAME . '.service\'';
		$stderr = 'Unit unknown.service could not be found.';
		$this->receiveCommand(
			command: $command,
			needSudo: true,
			stderr: $stderr,
			exitCode: 4,
		);
		Assert::throws(function (): void {
			$this->manager->getStatus('unknown');
		}, NonexistentServiceException::class, $stderr);
	}

	/**
	 * Set up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->setUpCommandExecutor();
		$this->manager = new SystemDManager($this->commandExecutor);
	}

}

$test = new SystemDManagerTest();
$test->run();
