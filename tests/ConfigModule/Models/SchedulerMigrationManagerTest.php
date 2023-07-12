<?php

/**
 * TEST: App\ConfigModule\Models\SchedulerMigrationManager
 * @covers App\ConfigModule\Models\SchedulerMigrationManager
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

namespace Tests\ConfigModule\Models;

use App\ConfigModule\Models\MainManager;
use App\ConfigModule\Models\SchedulerMigrationManager;
use App\ConfigModule\Models\SchedulerSchemaManager;
use App\CoreModule\Entities\CommandStack;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\FileManager;
use App\CoreModule\Models\ZipArchiveManager;
use Mockery;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;
use ZipArchive;

require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for scheduler's configuration migration manager
 */
final class SchedulerMigrationManagerTest extends TestCase {

	/**
	 * Path to a directory with scheduler's configuration
	 */
	private const CONFIG_PATH = TESTER_DIR . '/data/scheduler/';

	/**
	 * Path to a temporary directory with scheduler's configuration
	 */
	private const CONFIG_TEMP_PATH = TMP_DIR . '/migrations/scheduler/';

	/**
	 * Path to the ZIP archive with IQRF Gateway Daemon's configuration
	 */
	private const ZIP_PATH = TESTER_DIR . '/data/iqrf-gateway-scheduler.zip';

	/**
	 * Path to the temporary ZIP archive with IQRF Gateway Daemon's configuration
	 */
	private const ZIP_TEMP_PATH = TMP_DIR . '/iqrf-gateway-scheduler.zip';

	/**
	 * @var FileManager Text file manager
	 */
	private FileManager $fileManager;

	/**
	 * @var SchedulerMigrationManager Configuration migration manager
	 */
	private SchedulerMigrationManager $manager;

	/**
	 * Test function to download IQRF Gateway Daemon's configuration in a ZIP archive
	 */
	public function testCreateArchive(): void {
		$actual = $this->manager->createArchive();
		$files = $this->createList(self::CONFIG_PATH);
		$zipManager = new ZipArchiveManager($actual, ZipArchive::CREATE);
		foreach ($files as $file) {
			$expected = $this->fileManager->read($file);
			Assert::same($expected, $zipManager->openFile($file));
		}
		$zipManager->close();
	}

	/**
	 * Test function to extracts an archive with scheduler configuration (success)
	 */
	public function testExtractArchiveSuccess(): void {
		$this->manager->extractArchive(self::ZIP_TEMP_PATH);
		$expected = $this->createList(self::CONFIG_PATH);
		$actual = $this->createList(self::CONFIG_TEMP_PATH);
		Assert::same($expected, $actual);
	}

	/**
	 * Set up the test environment
	 */
	protected function setUp(): void {
		Environment::lock('migration', TMP_DIR);
		$this->copyFiles();
		$commandStack = new CommandStack();
		$commandManager = new CommandManager(false, $commandStack);
		$this->fileManager = new FileManager(self::CONFIG_PATH, $commandManager);
		$mainConfigManager = Mockery::mock(MainManager::class);
		$mainConfigManager->shouldReceive('getCacheDir')
			->andReturn(self::CONFIG_TEMP_PATH . '/../');
		$schemaManager = Mockery::mock(SchedulerSchemaManager::class);
		$schemaManager->shouldReceive('validate')
			->andReturn(true);
		$commandManager = Mockery::mock(CommandManager::class);
		$this->manager = new SchedulerMigrationManager($mainConfigManager, $schemaManager, $commandManager);
	}

	/**
	 * Cleanup the test environment
	 */
	protected function tearDown(): void {
		Mockery::close();
	}

	/**
	 * Copy files for testing
	 */
	private function copyFiles(): void {
		FileSystem::copy(self::CONFIG_PATH, self::CONFIG_TEMP_PATH);
		FileSystem::copy(self::ZIP_PATH, self::ZIP_TEMP_PATH);
	}

	/**
	 * Create list of files
	 * @param string $path Path to the directory
	 * @return array<string> List of files in the directory
	 */
	private function createList(string $path): array {
		$path = realpath($path) . '/';
		$list = [];
		foreach (Finder::findFiles('*.json')->from($path) as $file) {
			$list[] = str_replace($path, '', $file->getRealPath());
		}
		sort($list);
		return $list;
	}

}

$test = new SchedulerMigrationManagerTest();
$test->run();
