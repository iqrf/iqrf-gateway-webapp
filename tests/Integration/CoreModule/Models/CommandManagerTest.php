<?php

/**
 * TEST: App\CoreModule\Models\CommandManager
 * @covers App\CoreModule\Models\CommandManager
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

namespace Tests\Integration\CoreModule\Models;

use App\CoreModule\Entities\CommandStack;
use App\CoreModule\Models\CommandManager;
use Nette\Utils\Strings;
use Symfony\Component\Process\Process;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for command manager
 */
final class CommandManagerTest extends TestCase {

	/**
	 * @var string Executed command
	 */
	private const COMMAND = 'echo "OK"';

	/**
	 * @var CommandManager Command manager
	 */
	private CommandManager $manager;

	/**
	 * Tests the function to execute a shell command
	 */
	public function testRun(): void {
		$actual = $this->manager->run(self::COMMAND);
		Assert::same(self::COMMAND, $actual->getCommand());
		Assert::same('OK', $actual->getStdout());
		Assert::same('', $actual->getStderr());
		Assert::same(0, $actual->getExitCode());
	}

	/**
	 * Tests the function to execute a shell command asynchronously
	 */
	public function testRunAsync(): void {
		$this->manager->runAsync(static function (string $type, ?string $buffer): void {
			Assert::same(Process::OUT, $type);
			Assert::same('OK', Strings::trim($buffer));
		}, self::COMMAND, false, 10);
	}

	/**
	 * Tests the function to check the existence of a command (fail)
	 */
	public function testCommandExistFail(): void {
		Assert::false($this->manager->commandExist('sndikasdhisdbajdbas'));
	}

	/**
	 * Tests the function to check the existence of a command (success)
	 */
	public function testCommandExistSuccess(): void {
		Assert::true($this->manager->commandExist('echo'));
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$commandStack = new CommandStack();
		$this->manager = new CommandManager(false, $commandStack);
	}

}

$test = new CommandManagerTest();
$test->run();
