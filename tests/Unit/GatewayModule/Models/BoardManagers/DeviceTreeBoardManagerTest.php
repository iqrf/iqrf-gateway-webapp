<?php

/**
 * TEST: App\GatewayModule\Models\DeviceTreeBoardManager
 * @covers App\GatewayModule\Models\DeviceTreeBoardManager
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

namespace Tests\Unit\GatewayModule\Models\BoardManagers;

use App\GatewayModule\Models\BoardManagers\DeviceTreeBoardManager;
use Tester\Assert;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for Device tree board manager
 */
final class DeviceTreeBoardManagerTest extends CommandTestCase {

	/**
	 * @var DeviceTreeBoardManager Device tree board manager
	 */
	private DeviceTreeBoardManager $manager;

	/**
	 * @var string Executed command
	 */
	private const COMMAND = 'cat /proc/device-tree/model';

	/**
	 * Tests the function to get board's name from DMI (success)
	 */
	public function testGetNameSuccess(): void {
		$expected = 'Raspberry Pi 2 Models B Rev 1.1';
		$this->receiveCommand(self::COMMAND, true, $expected);
		Assert::same($expected, $this->manager->getName());
	}

	/**
	 * Tests the function to get board's name from DMI (fail)
	 */
	public function testGetNameFail(): void {
		$this->receiveCommand(self::COMMAND, true);
		Assert::null($this->manager->getName());
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new DeviceTreeBoardManager($this->commandManager);
	}

}

$test = new DeviceTreeBoardManagerTest();
$test->run();
