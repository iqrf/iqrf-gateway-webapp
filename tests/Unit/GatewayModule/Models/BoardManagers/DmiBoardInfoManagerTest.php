<?php

/**
 * TEST: App\GatewayModule\Models\DmiBoardManager
 * @covers App\GatewayModule\Models\DmiBoardManager
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

use App\GatewayModule\Models\BoardManagers\DmiBoardManager;
use Iqrf\CommandExecutor\Tester\Traits\CommandExecutorTestCase;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for DMI board manager
 */
final class DmiBoardInfoManagerTest extends TestCase {

	use CommandExecutorTestCase;

	/**
	 * Executed commands
	 */
	private const COMMANDS = [
		'dmiBoardName' => 'cat /sys/class/dmi/id/board_name',
		'dmiBoardVendor' => 'cat /sys/class/dmi/id/board_vendor',
		'dmiBoardVersion' => 'cat /sys/class/dmi/id/board_version',
	];

	/**
	 * @var DmiBoardManager DMI board manager
	 */
	private DmiBoardManager $manager;

	/**
	 * Tests the function to get board's name from DMI (success)
	 */
	public function testGetNameSuccess(): void {
		$this->receiveCommand(
			command: self::COMMANDS['dmiBoardVendor'],
			needSudo: true,
			stdout: 'AAEON',
		);
		$this->receiveCommand(
			command: self::COMMANDS['dmiBoardName'],
			needSudo: true,
			stdout: 'UP-APL01',
		);
		$this->receiveCommand(
			command: self::COMMANDS['dmiBoardVersion'],
			needSudo: true,
			stdout: 'V0.4',
		);
		Assert::same('AAEON UP-APL01 (V0.4)', $this->manager->getName());
	}

	/**
	 * Tests the function to get board's name from DMI (without version)
	 */
	public function testGetNameWithoutVersion(): void {
		$this->receiveCommand(
			command: self::COMMANDS['dmiBoardVendor'],
			needSudo: true,
			stdout: 'ASRock',
		);
		$this->receiveCommand(
			command: self::COMMANDS['dmiBoardName'],
			needSudo: true,
			stdout: 'X570 Extreme4',
		);
		$this->receiveCommand(
			command: self::COMMANDS['dmiBoardVersion'],
			needSudo: true,
		);
		Assert::same('ASRock X570 Extreme4', $this->manager->getName());
	}

	/**
	 * Tests the function to get board's name from DMI (fail)
	 */
	public function testGetNameFail(): void {
		$this->receiveCommand(
			command: self::COMMANDS['dmiBoardVendor'],
			needSudo: true,
		);
		$this->receiveCommand(
			command: self::COMMANDS['dmiBoardName'],
			needSudo: true,
		);
		$this->receiveCommand(
			command: self::COMMANDS['dmiBoardVersion'],
			needSudo: true,
		);
		Assert::null($this->manager->getName());
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->setUpCommandExecutor();
		$this->manager = new DmiBoardManager($this->commandExecutor);
	}

}

$test = new DmiBoardInfoManagerTest();
$test->run();
