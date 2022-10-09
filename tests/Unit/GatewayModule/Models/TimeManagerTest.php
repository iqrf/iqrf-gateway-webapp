<?php

/**
 * TEST: App\GatewayModule\Models\TimeManager
 * @covers App\GatewayModule\Models\TimeManager
 * @phpVersion >= 7.4
 * @testCase
 */
/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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

namespace Tests\Unit\GatewayModule\Models;

use App\GatewayModule\Exceptions\NonexistentTimezoneException;
use App\GatewayModule\Exceptions\TimeDateException;
use App\GatewayModule\Models\TimeManager;
use Tester\Assert;
use Tests\Stubs\CoreModule\Models\Command;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for time manager
 */
final class TimeManagerTest extends CommandTestCase {

	/**
	 * @var array<string, string> Commands to be executed
	 */
	private const COMMANDS = [
		'timestamp' => 'date +%s',
		'status' => 'timedatectl show',
		'listTimezones' => 'timedatectl list-timezones',
		'setTimezone' => 'timedatectl set-timezone UTC',
		'setTimezoneNonexistent' => 'timedatectl set-timezone Nonexistent/Nonexistent',
		'setNtp' => 'timedatectl set-ntp true',
	];

	/**
	 * @var TimeManager Time manager
	 */
	private TimeManager $manager;

	/**
	 * Sets up test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$path = __DIR__ . '../../data/systemd/conf/timesyncd.conf';
		$this->manager = new TimeManager($this->commandManager, $path);
	}

	/**
	 * Tests the function to get timedatectl status
	 */
	public function testGetStatus(): void {
		$expected = [
			'Timezone' => 'UTC',
			'NTPSynchronized' => true,
		];
		$command = new Command(self::COMMANDS['status'], 'Timezone=UTC' . PHP_EOL . 'NTPSynchronized=yes', '', 0);
		$this->commandManager->shouldReceive('run')
			->withArgs([self::COMMANDS['status']])
			->andReturn($command);
		Assert::same($expected, $this->manager->getStatus());
	}

	/**
	 * Tests the function to get timedatectl status with exception thrown
	 */
	public function testGetStatusException(): void {
		$command = new Command(self::COMMANDS['status'], '', '', 1);
		$this->commandManager->shouldReceive('run')
			->withArgs([self::COMMANDS['status']])
			->andReturn($command);
		Assert::throws(function (): void {
			$this->manager->getStatus();
		}, TimeDateException::class);
	}

	/**
	 * Tests the function to retrieve available timezones
	 */
	public function testAvailableTimezones(): void {
		$timezones = 'UTC';
		$command = new Command(self::COMMANDS['listTimezones'], $timezones, '', 0);
		$this->commandManager->shouldReceive('run')
			->andReturn($command);
		$expected = [
			[
				'name' => 'UTC',
				'code' => 'UTC',
				'offset' => '+0000',
			],
		];
		Assert::same($expected, $this->manager->availableTimezones());
	}

	/**
	 * Tests the function to get timezone information
	 */
	public function testTimezoneInfo(): void {
		$expected = [
			'name' => 'UTC',
			'code' => 'UTC',
			'offset' => '+0000',
		];
		Assert::same($expected, $this->manager->timezoneInfo('UTC'));
	}

	/**
	 * Tests the function to set timezone
	 */
	public function testSetTimezone(): void {
		$command = new Command(self::COMMANDS['setTimezone'], '', '', 0);
		$this->commandManager->shouldReceive('run')
			->withArgs([self::COMMANDS['setTimezone'], true])
			->andReturn($command);
		Assert::noError(function (): void {
			$this->manager->setTimezone('UTC');
		});
	}

	/**
	 * Tests the function to set timezone with nonexistent timezone
	 */
	public function testSetTimezoneNonexistent(): void {
		$command = new Command(self::COMMANDS['setTimezoneNonexistent'], '', '', 1);
		$this->commandManager->shouldReceive('run')
			->withArgs([self::COMMANDS['setTimezoneNonexistent'], true])
			->andReturn($command);
		Assert::throws(function (): void {
			$this->manager->setTimezone('Nonexistent/Nonexistent');
		}, NonexistentTimezoneException::class);
	}

	/**
	 * Tests the function to set ntp synchronization
	 */
	public function testSetNtp(): void {
		$command = new Command(self::COMMANDS['setNtp'], '', '', 0);
		$this->commandManager->shouldReceive('run')
			->withArgs([self::COMMANDS['setNtp'], true])
			->andReturn($command);
		Assert::noError(function (): void {
			$this->manager->setNtp(true);
		});
	}

}

$test = new TimeManagerTest();
$test->run();
