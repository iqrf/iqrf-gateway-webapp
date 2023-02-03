<?php

/**
 * TEST: App\GatewayModule\Models\DmiBoardManager
 * @covers App\GatewayModule\Models\DmiBoardManager
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

namespace Tests\Unit\GatewayModule\Models\BoardManagers;

use App\GatewayModule\Models\BoardManagers\DmiBoardManager;
use Tester\Assert;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for DMI board manager
 */
final class DmiBoardInfoManagerTest extends CommandTestCase {

	/**
	 * @var DmiBoardManager DMI board manager
	 */
	private DmiBoardManager $manager;

	/**
	 * @var array<string, string> Executed commands
	 */
	private const COMMANDS = [
		'dmiBoardName' => 'cat /sys/class/dmi/id/board_name',
		'dmiBoardVendor' => 'cat /sys/class/dmi/id/board_vendor',
		'dmiBoardVersion' => 'cat /sys/class/dmi/id/board_version',
	];

	/**
	 * Tests the function to get board's name from DMI (success)
	 */
	public function testGetNameSuccess(): void {
		$this->receiveCommand(self::COMMANDS['dmiBoardVendor'], true, 'AAEON');
		$this->receiveCommand(self::COMMANDS['dmiBoardName'], true, 'UP-APL01');
		$this->receiveCommand(self::COMMANDS['dmiBoardVersion'], true, 'V0.4');
		Assert::same('AAEON UP-APL01 (V0.4)', $this->manager->getName());
	}

	/**
	 * Tests the function to get board's name from DMI (without version)
	 */
	public function testGetNameWithoutVersion(): void {
		$this->receiveCommand(self::COMMANDS['dmiBoardVendor'], true, 'ASRock');
		$this->receiveCommand(self::COMMANDS['dmiBoardName'], true, 'X570 Extreme4');
		$this->receiveCommand(self::COMMANDS['dmiBoardVersion'], true);
		Assert::same('ASRock X570 Extreme4', $this->manager->getName());
	}

	/**
	 * Tests the function to get board's name from DMI (fail)
	 */
	public function testGetNameFail(): void {
		$this->receiveCommand(self::COMMANDS['dmiBoardVendor'], true);
		$this->receiveCommand(self::COMMANDS['dmiBoardName'], true);
		$this->receiveCommand(self::COMMANDS['dmiBoardVersion'], true);
		Assert::null($this->manager->getName());
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new DmiBoardManager($this->commandManager);
	}

}

$test = new DmiBoardInfoManagerTest();
$test->run();
