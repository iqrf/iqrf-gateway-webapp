<?php

/**
 * TEST: App\CoreModule\Models\PrivilegedFileManager
 * @covers App\CoreModule\Models\PrivilegedFileManager
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

namespace Tests\Integration\CoreModule\Models;

use App\CoreModule\Entities\CommandStack;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\PrivilegedFileManager;
use Nette\IOException;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for privileged text file manager
 */
final class PrivilegedFileManagerTest extends TestCase {

	/**
	 * @var string File name
	 */
	private const FILE_NAME = 'config.json';

	/**
	 * @var string Directory with configuration files
	 */
	private const CONFIG_PATH = TESTER_DIR . '/data/configuration/';

	/**
	 * @var string Directory with temporary configuration files
	 */
	private const CONFIG_TEMP_PATH = TMP_DIR . '/configuration/';

	/**
	 * @var CommandStack Command stack
	 */
	private CommandStack $commandStack;

	/**
	 * @var PrivilegedFileManager Privileged text file manager
	 */
	private PrivilegedFileManager $manager;

	/**
	 * @var PrivilegedFileManager Privileged text file manager
	 */
	private PrivilegedFileManager $managerTest;

	/**
	 * Tests the function to read a text file
	 */
	public function testRead(): void {
		$expected = FileSystem::read(self::CONFIG_PATH . self::FILE_NAME);
		Assert::equal($expected, $this->manager->read(self::FILE_NAME) . PHP_EOL);
		$commands = $this->commandStack->getCommands();
		Assert::equal(1, count($commands));
		Assert::equal('cat \'' . self::CONFIG_PATH . self::FILE_NAME . '\'', $commands[0]->getCommand());
		Assert::equal(0, $commands[0]->getExitCode());
		Assert::equal('', $commands[0]->getStderr());
		Assert::equal($expected, $commands[0]->getStdout() . PHP_EOL);
	}

	/**
	 * Tests the function to read a text file (nonexistent file)
	 */
	public function testReadNonexistent(): void {
		Assert::exception(function (): void {
			$this->manager->read('nonsense');
		}, IOException::class, 'cat: ' . self::CONFIG_PATH . 'nonsense: No such file or directory');
		$commands = $this->commandStack->getCommands();
		Assert::equal(1, count($commands));
		Assert::equal('cat \'' . self::CONFIG_PATH . 'nonsense\'', $commands[0]->getCommand());
		Assert::equal(1, $commands[0]->getExitCode());
		Assert::equal('cat: ' . self::CONFIG_PATH . 'nonsense: No such file or directory', $commands[0]->getStderr());
		Assert::equal('', $commands[0]->getStdout());
	}

	/**
	 * Tests the function to check if the file exists (the file is not exist)
	 */
	public function testExistsFail(): void {
		Assert::false($this->manager->exists('nonsense'));
		$commands = $this->commandStack->getCommands();
		Assert::equal(1, count($commands));
		Assert::equal('test -e \'' . self::CONFIG_PATH . 'nonsense\'', $commands[0]->getCommand());
		Assert::equal(1, $commands[0]->getExitCode());
		Assert::equal('', $commands[0]->getStderr());
		Assert::equal('', $commands[0]->getStdout());
	}

	/**
	 * Tests the function to check if the file exists (the file is existing)
	 */
	public function testExistsSuccess(): void {
		Assert::true($this->manager->exists(self::FILE_NAME));
		$commands = $this->commandStack->getCommands();
		Assert::equal(1, count($commands));
		Assert::equal('test -e \'' . self::CONFIG_PATH . self::FILE_NAME . '\'', $commands[0]->getCommand());
		Assert::equal(0, $commands[0]->getExitCode());
		Assert::equal('', $commands[0]->getStderr());
		Assert::equal('', $commands[0]->getStdout());
	}

	/**
	 * Tests the function to write a text file
	 */
	public function testWrite(): void {
		$fileName = 'config-test.json';
		$expected = FileSystem::read(self::CONFIG_PATH . self::FILE_NAME);
		$this->managerTest->write($fileName, $expected);
		Assert::equal($expected, $this->managerTest->read($fileName) . PHP_EOL);
		$commands = $this->commandStack->getCommands();
		Assert::equal(3, count($commands));
		Assert::equal('mkdir -p \'' . self::CONFIG_TEMP_PATH . '\'', $commands[0]->getCommand());
		Assert::equal(0, $commands[0]->getExitCode());
		Assert::equal('', $commands[0]->getStderr());
		Assert::equal('', $commands[0]->getStdout());
		Assert::equal('tee \'' . self::CONFIG_TEMP_PATH . $fileName . '\'', $commands[1]->getCommand());
		Assert::equal(0, $commands[1]->getExitCode());
		Assert::equal('', $commands[1]->getStderr());
		Assert::equal($expected, $commands[1]->getStdout() . PHP_EOL);
		Assert::equal('cat \'' . self::CONFIG_TEMP_PATH . $fileName . '\'', $commands[2]->getCommand());
		Assert::equal(0, $commands[2]->getExitCode());
		Assert::equal('', $commands[2]->getStderr());
		Assert::equal($expected, $commands[2]->getStdout() . PHP_EOL);
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->commandStack = new CommandStack();
		$commandManager = new CommandManager(false, $this->commandStack);
		$this->manager = new PrivilegedFileManager(self::CONFIG_PATH, $commandManager);
		$this->managerTest = new PrivilegedFileManager(self::CONFIG_TEMP_PATH, $commandManager);
	}

}

$test = new PrivilegedFileManagerTest();
$test->run();
