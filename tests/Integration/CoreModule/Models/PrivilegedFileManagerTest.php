<?php

/**
 * TEST: App\CoreModule\Models\PrivilegedFileManager
 * @covers App\CoreModule\Models\PrivilegedFileManager
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

namespace Tests\Integration\CoreModule\Models;

use App\CoreModule\Entities\CommandStack;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\PrivilegedFileManager;
use Nette\IOException;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for privileged text file manager
 */
final class PrivilegedFileManagerTest extends TestCase {

	/**
	 * File name
	 */
	private const FILE_NAME = 'config.json';

	/**
	 * File name of nonexistent file
	 */
	private const FILE_NAME_NONEXISTENT = 'nonexistent.json';

	/**
	 * File name of symbolic link
	 */
	private const FILE_NAME_SYMLINK = 'symlink.json';

	/**
	 * Directory with configuration files
	 */
	private const CONFIG_PATH = TESTER_DIR . '/data/configuration';

	/**
	 * Directory with temporary configuration files
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
	 * Tests the function to get a base path
	 */
	public function testGetBasePath(): void {
		Assert::same(self::CONFIG_PATH, $this->manager->getBasePath());
	}

	/**
	 * Tests the function to create a symbolic link
	 */
	public function testCreateSymLink(): void {
		Environment::lock('fileManager_symlink', TMP_DIR);
		$this->managerTest->delete(self::FILE_NAME_SYMLINK);
		$this->commandStack->clearCommands();
		$this->managerTest->createSymLink(self::FILE_NAME, self::FILE_NAME_SYMLINK);
		Assert::true(is_link(self::CONFIG_TEMP_PATH . self::FILE_NAME_SYMLINK));
		$commands = $this->commandStack->getCommands();
		Assert::equal(2, count($commands));
		Assert::equal('rm -rf \'' . self::CONFIG_TEMP_PATH . self::FILE_NAME_SYMLINK . '\'', $commands[0]->getCommand());
		Assert::equal(0, $commands[0]->getExitCode());
		Assert::equal('', $commands[0]->getStderr());
		Assert::equal('', $commands[0]->getStdout());
		Assert::equal('ln -s \'' . self::CONFIG_TEMP_PATH . self::FILE_NAME . '\' \'' . self::CONFIG_TEMP_PATH . self::FILE_NAME_SYMLINK . '\'', $commands[1]->getCommand());
		Assert::equal(0, $commands[1]->getExitCode());
		Assert::equal('', $commands[1]->getStderr());
		Assert::equal('', $commands[1]->getStdout());
	}

	/**
	 * Tests the function to check if a file is a symbolic link
	 */
	public function testIsSymLink(): void {
		Environment::lock('fileManager_symlink', TMP_DIR);
		$this->managerTest->delete(self::FILE_NAME_SYMLINK);
		$this->commandStack->clearCommands();
		Assert::false($this->managerTest->isSymLink(self::FILE_NAME_SYMLINK));
		symlink(self::CONFIG_PATH . '/' . self::FILE_NAME, self::CONFIG_TEMP_PATH . '/' . self::FILE_NAME_SYMLINK);
		Assert::true($this->managerTest->isSymLink(self::FILE_NAME_SYMLINK));
		$commands = $this->commandStack->getCommands();
		Assert::equal(2, count($commands));
		Assert::equal('test -L \'' . self::CONFIG_TEMP_PATH . self::FILE_NAME_SYMLINK . '\'', $commands[0]->getCommand());
		Assert::equal(1, $commands[0]->getExitCode());
		Assert::equal('', $commands[0]->getStderr());
		Assert::equal('', $commands[0]->getStdout());
		Assert::equal('test -L \'' . self::CONFIG_TEMP_PATH . self::FILE_NAME_SYMLINK . '\'', $commands[1]->getCommand());
		Assert::equal(0, $commands[1]->getExitCode());
		Assert::equal('', $commands[1]->getStderr());
		Assert::equal('', $commands[1]->getStdout());
	}

	/**
	 * Tests the function to read a text file
	 */
	public function testRead(): void {
		$expected = FileSystem::read(self::CONFIG_PATH . '/' . self::FILE_NAME);
		Assert::equal($expected, $this->manager->read(self::FILE_NAME) . PHP_EOL);
		$commands = $this->commandStack->getCommands();
		Assert::equal(1, count($commands));
		Assert::equal('cat \'' . self::CONFIG_PATH . '/' . self::FILE_NAME . '\'', $commands[0]->getCommand());
		Assert::equal(0, $commands[0]->getExitCode());
		Assert::equal('', $commands[0]->getStderr());
		Assert::equal($expected, $commands[0]->getStdout() . PHP_EOL);
	}

	/**
	 * Tests the function to read a text file (nonexistent file)
	 */
	public function testReadNonexistent(): void {
		$path = self::CONFIG_PATH . '/' . self::FILE_NAME_NONEXISTENT;
		Assert::exception(function (): void {
			$this->manager->read(self::FILE_NAME_NONEXISTENT);
		}, IOException::class, 'cat: ' . $path . ': No such file or directory');
		$commands = $this->commandStack->getCommands();
		Assert::equal(1, count($commands));
		Assert::equal('cat \'' . $path . '\'', $commands[0]->getCommand());
		Assert::equal(1, $commands[0]->getExitCode());
		Assert::equal('cat: ' . $path . ': No such file or directory', $commands[0]->getStderr());
		Assert::equal('', $commands[0]->getStdout());
	}

	/**
	 * Tests the function to check if the file exists (the file is not exist)
	 */
	public function testExistsFail(): void {
		Assert::false($this->manager->exists(self::FILE_NAME_NONEXISTENT));
		$commands = $this->commandStack->getCommands();
		Assert::equal(1, count($commands));
		Assert::equal('test -e \'' . self::CONFIG_PATH . '/' . self::FILE_NAME_NONEXISTENT . '\'', $commands[0]->getCommand());
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
		Assert::equal('test -e \'' . self::CONFIG_PATH . '/' . self::FILE_NAME . '\'', $commands[0]->getCommand());
		Assert::equal(0, $commands[0]->getExitCode());
		Assert::equal('', $commands[0]->getStderr());
		Assert::equal('', $commands[0]->getStdout());
	}

	/**
	 * Tests the function to write a text file
	 */
	public function testWrite(): void {
		Environment::lock('fileManager_write', TMP_DIR);
		$fileName = 'config-test.json';
		$expected = FileSystem::read(self::CONFIG_PATH . '/' . self::FILE_NAME);
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
	 * Tests the function to list directories
	 */
	public function testListDirectories(): void {
		$expected = [
			'scheduler',
		];
		Assert::equal($expected, $this->manager->listDirectories());
		$commands = $this->commandStack->getCommands();
		Assert::equal(1, count($commands));
		Assert::equal('find \'' . self::CONFIG_PATH . '\' -type d -printf \'%P\n\'', $commands[0]->getCommand());
		Assert::equal(0, $commands[0]->getExitCode());
		Assert::equal('', $commands[0]->getStderr());
		Assert::equal(implode(PHP_EOL, $expected), $commands[0]->getStdout());
	}

	/**
	 * Tests the function to list files
	 */
	public function testListFiles(): void {
		$expected = [
			'config.json',
			'iqrf__AutonetworkService.json',
			'iqrf__BondNodeLocalService.json',
			'iqrf__EnumerateDeviceService.json',
			'iqrf__IdeCounterpart.json',
			'iqrf__IqrfCdc.json',
			'iqrf__IqrfDpa.json',
			'iqrf__IqrfInfo.json',
			'iqrf__IqrfSpi.json',
			'iqrf__IqrfUart.json',
			'iqrf__JsCache.json',
			'iqrf__JsRenderDuktape.json',
			'iqrf__JsonCfgApi.json',
			'iqrf__JsonDpaApiIqrfStandard.json',
			'iqrf__JsonDpaApiIqrfStdExt.json',
			'iqrf__JsonDpaApiRaw.json',
			'iqrf__JsonIqrfInfoApi.json',
			'iqrf__JsonMngApi.json',
			'iqrf__JsonSplitter.json',
			'iqrf__MonitorService.json',
			'iqrf__MqMessaging.json',
			'iqrf__MqttMessaging.json',
			'iqrf__OtaUploadService.json',
			'iqrf__ReadTrConfService.json',
			'iqrf__RemoveBondService.json',
			'iqrf__Scheduler.json',
			'iqrf__SchedulerMessaging.json',
			'iqrf__SmartConnectService.json',
			'iqrf__UdpMessaging.json',
			'iqrf__WebsocketMessaging.json',
			'iqrf__WriteTrConfService.json',
			'scheduler/Tasks.json',
			'shape__ConfigurationService.json',
			'shape__CurlRestApiService.json',
			'shape__LauncherService.json',
			'shape__TraceFileService.json',
			'shape__TraceFileService_JsCache.json',
			'shape__TraceFormatService.json',
			'shape__WebsocketCppService.json',
			'shape__WebsocketCppService_Monitor.json',
		];
		$actual = $this->manager->listFiles();
		sort($actual);
		Assert::equal($expected, $actual);
		$commands = $this->commandStack->getCommands();
		Assert::equal(1, count($commands));
		Assert::equal('find \'' . self::CONFIG_PATH . '\' -type f -printf \'%P\n\'', $commands[0]->getCommand());
		Assert::equal(0, $commands[0]->getExitCode());
		Assert::equal('', $commands[0]->getStderr());
		$stdout = explode(PHP_EOL, $commands[0]->getStdout());
		sort($stdout);
		Assert::equal($expected, $stdout);
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
