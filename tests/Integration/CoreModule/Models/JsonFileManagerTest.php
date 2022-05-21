<?php

/**
 * TEST: App\CoreModule\Models\JsonFileManager
 * @covers App\CoreModule\Models\JsonFileManager
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
use App\CoreModule\Models\JsonFileManager;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for JSON file manager
 */
final class JsonFileManagerTest extends TestCase {

	/**
	 * Directory with configuration files
	 */
	private const CONFIG_PATH = TESTER_DIR . '/data/configuration/';

	/**
	 * Directory with temporary configuration files
	 */
	private const CONFIG_TEMP_PATH = TMP_DIR . '/configuration/';

	/**
	 * File name
	 */
	private const FILE_NAME = 'config';

	/**
	 * @var JsonFileManager JSON File manager
	 */
	private JsonFileManager $manager;

	/**
	 * @var JsonFileManager JSON File manager
	 */
	private JsonFileManager $managerTest;

	/**
	 * Tests the function to get a directory with files
	 */
	public function testGetDirectory(): void {
		Assert::same(self::CONFIG_PATH, $this->manager->getDirectory());
	}

	/**
	 * Tests the function to delete the JSON file
	 */
	public function testDelete(): void {
		$fileName = 'test-delete';
		FileSystem::copy(self::CONFIG_PATH . self::FILE_NAME . '.json', self::CONFIG_TEMP_PATH . $fileName . '.json');
		Assert::true($this->managerTest->exists($fileName));
		$this->managerTest->delete($fileName);
		Assert::false($this->managerTest->exists($fileName));
	}

	/**
	 * Tests the function to check if the JSON file exists (the file is not exist)
	 */
	public function testExistsFail(): void {
		Assert::false($this->manager->exists('nonsense'));
	}

	/**
	 * Tests the function to check if the JSON file exists (the file is exist)
	 */
	public function testExistsSuccess(): void {
		Assert::true($this->manager->exists(self::FILE_NAME));
	}

	/**
	 * Tests the function to read a JSON file
	 */
	public function testRead(): void {
		$text = FileSystem::read(self::CONFIG_PATH . self::FILE_NAME . '.json');
		$expected = Json::decode($text, Json::FORCE_ARRAY);
		Assert::equal($expected, $this->manager->read(self::FILE_NAME));
	}

	/**
	 * Tests the function to write a JSON file
	 */
	public function testWrite(): void {
		$fileName = 'config-test';
		$expected = $this->manager->read(self::FILE_NAME);
		$this->managerTest->write($fileName, $expected);
		Assert::equal($expected, $this->managerTest->read($fileName));
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$commandStack = new CommandStack();
		$commandManager = new CommandManager(false, $commandStack);
		$this->manager = new JsonFileManager(self::CONFIG_PATH, $commandManager);
		$this->managerTest = new JsonFileManager(self::CONFIG_TEMP_PATH, $commandManager);
	}

}

$test = new JsonFileManagerTest();
$test->run();
