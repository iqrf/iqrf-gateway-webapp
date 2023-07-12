<?php

/**
 * TEST: App\CoreModule\Entities\CommandStack
 * @covers App\CoreModule\Entities\CommandStack
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

namespace Tests\Integration\CoreModule\Entities;

use App\CoreModule\Entities\Command;
use App\CoreModule\Entities\CommandStack;
use Symfony\Component\Process\Process;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for command stack entity
 */
final class CommandStackTest extends TestCase {

	/**
	 * Command
	 */
	private const COMMAND = 'ls -al';

	/**
	 * @var Command Command entity
	 */
	private Command $entity;

	/**
	 * @var CommandStack Command stack
	 */
	private CommandStack $stack;

	/**
	 * Tests the function to return commands in stack (empty stack)
	 */
	public function testGetCommandsEmpty(): void {
		Assert::same([], $this->stack->getCommands());
	}

	/**
	 * Tests the function to add command into stack
	 */
	public function testAddCommand(): void {
		$this->stack->addCommand($this->entity);
		$commands = $this->stack->getCommands();
		Assert::same(1, count($commands));
		Assert::same(self::COMMAND, $commands[0]->getCommand());
	}

	/**
	 * Sets up the testing environment
	 */
	protected function setUp(): void {
		$process = Process::fromShellCommandline(self::COMMAND);
		$process->run();
		$this->entity = new Command(self::COMMAND, $process);
		$this->stack = new CommandStack();
		parent::setUp();
	}

}

$test = new CommandStackTest();
$test->run();
