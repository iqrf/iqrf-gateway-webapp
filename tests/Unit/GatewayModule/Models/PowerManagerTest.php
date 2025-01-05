<?php

/**
 * TEST: App\GatewayModule\Models\PowerManager
 * @covers App\GatewayModule\Models\PowerManager
 * @phpVersion >= 7.4
 * @testCase
 */
/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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

use App\GatewayModule\Models\PowerManager;
use Tester\Assert;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for tool for powering off and rebooting IQRF Gateway
 */
final class PowerManagerTest extends CommandTestCase {

	/**
	 * @var PowerManager Tool for powering off and rebooting IQRF Gateway
	 */
	private PowerManager $manager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new PowerManager($this->commandManager);
	}

	/**
	 * Tests the function to power off IQRF Gateway
	 */
	public function testPowerOff(): void {
		$this->receiveCommand('shutdown -P `date --date "now + 60 seconds" "+%H:%M"`', true);
		Assert::noError(function (): void {
			$this->manager->powerOff();
		});
	}

	/**
	 * Tests the function to reboot IQRF Gateway
	 */
	public function testReboot(): void {
		$this->receiveCommand('shutdown -r `date --date "now + 60 seconds" "+%H:%M"`', true);
		Assert::noError(function (): void {
			$this->manager->reboot();
		});
	}

}

$test = new PowerManagerTest();
$test->run();
