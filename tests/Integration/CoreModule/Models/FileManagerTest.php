<?php

/**
 * TEST: App\CoreModule\Models\FileManager
 * @covers App\CoreModule\Models\FileManager
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
use App\CoreModule\Models\FileManager;
use Nette\IOException;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for text file manager
 */
final class FileManagerTest extends TestCase {

	/**
	 * @var string File name
	 */
	private const FILE_NAME = 'config.json';

	/**
	 * @var string File name of nonexistent file
	 */
	private const FILE_NAME_NONEXISTENT = 'nonexistent.json';

	/**
	 * @var string Directory with configuration files
	 */
	private const CONFIG_PATH = TESTER_DIR . '/data/configuration/';

	/**
	 * @var string Directory with temporary configuration files
	 */
	private const CONFIG_TEMP_PATH = TMP_DIR . '/configuration/';

	/**
	 * @var FileManager Text file manager
	 */
	private FileManager $manager;

	/**
	 * @var FileManager Text file manager
	 */
	private FileManager $managerTest;

	/**
	 * Tests the function to get a base path
	 */
	public function testGetBasePath(): void {
		Assert::same(self::CONFIG_PATH, $this->manager->getBasePath());
	}

	/**
	 * Tests the function to delete a file
	 */
	public function testDelete(): void {
		$fileName = 'test-delete.json';
		$this->managerTest->write($fileName, $this->manager->read(self::FILE_NAME));
		Assert::true($this->managerTest->exists($fileName));
		$this->managerTest->delete($fileName);
		Assert::false($this->managerTest->exists($fileName));
	}

	/**
	 * Tests the function to delete a file (nonexistent)
	 */
	public function testDeleteNonexistent(): void {
		Assert::noError(function (): void {
			$this->manager->delete(self::FILE_NAME_NONEXISTENT);
		});
	}

	/**
	 * Tests the function to check if the file exists (the file is not exist)
	 */
	public function testExistsFail(): void {
		Assert::false($this->manager->exists(self::FILE_NAME_NONEXISTENT));
	}

	/**
	 * Tests the function to check if the file exists (the file is exist)
	 */
	public function testExistsSuccess(): void {
		Assert::true($this->manager->exists(self::FILE_NAME));
	}

	/**
	 * Tests the function to read a text file
	 */
	public function testRead(): void {
		$expected = FileSystem::read(self::CONFIG_PATH . self::FILE_NAME);
		Assert::equal($expected, $this->manager->read(self::FILE_NAME));
	}

	/**
	 * Tests the function to read a text file (nonexistent)
	 */
	public function testReadNonexistent(): void {
		if (PHP_MAJOR_VERSION >= 8) {
			$message = 'Unable to read file \'' . self::CONFIG_PATH . 'nonexistent.json\'. Failed to open stream: No such file or directory';
		} else {
			$message = 'Unable to read file \'' . self::CONFIG_PATH . 'nonexistent.json\'. failed to open stream: No such file or directory';
		}
		Assert::throws(function (): void {
			$this->manager->read(self::FILE_NAME_NONEXISTENT);
		}, IOException::class, $message);
	}

	/**
	 * Tests the function to read a JSON file
	 */
	public function testReadJson(): void {
		$text = FileSystem::read(self::CONFIG_PATH . self::FILE_NAME);
		$expected = Json::decode($text, Json::FORCE_ARRAY);
		Assert::equal($expected, $this->manager->readJson(self::FILE_NAME));
	}

	/**
	 * Tests the function to write a text file
	 */
	public function testWrite(): void {
		$fileName = 'config-test.json';
		$expected = $this->manager->read(self::FILE_NAME);
		$this->managerTest->write($fileName, $expected);
		Assert::equal($expected, $this->managerTest->read($fileName));
	}

	/**
	 * Tests the function to write a JSON file
	 */
	public function testWriteJson(): void {
		$fileName = 'config-test.json';
		$expected = $this->manager->readJson(self::FILE_NAME);
		$this->managerTest->writeJson($fileName, $expected);
		Assert::equal($expected, $this->managerTest->readJson($fileName));
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$commandStack = new CommandStack();
		$commandManager = new CommandManager(false, $commandStack);
		$this->manager = new FileManager(self::CONFIG_PATH, $commandManager);
		$this->managerTest = new FileManager(self::CONFIG_TEMP_PATH, $commandManager);
	}

}

$test = new FileManagerTest();
$test->run();
