<?php

/**
 * TEST: App\GatewayModule\Models\Backup\ControllerBackup
 * @covers App\GatewayModule\Models\Backup\ControllerBackup
 * @phpVersion >= 7.3
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

namespace Tests\Integration\GatewayModule\Models\Backup;

use App\CoreModule\Entities\CommandStack;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\FeatureManager;
use App\CoreModule\Models\ZipArchiveManager;
use App\GatewayModule\Models\Backup\ControllerBackup;
use Mockery;
use Mockery\MockInterface;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tester\Environment;
use Tests\Toolkit\TestCases\BackupTestCase;
use ZipArchive;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for controller backup manager
 */
final class ControllerBackupTest extends BackupTestCase {

	/**
	 * Path to controller configuration directory
	 */
	private const CONF_PATH = TESTER_DIR . '/data/controller/';

	/**
	 * Path to temporary controller configuration directory
	 */
	private const TEMP_CONF_PATH = TMP_DIR . '/backup/controller/';

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * @var FeatureManager|MockInterface Feature manager
	 */
	private $featureManager;

	/**
	 * @var ControllerBackup Controller backup manager
	 */
	private $controllerBackup;

	/**
	 * Tests the function to backup with disabled controller feature
	 */
	public function testBackupDisabled(): void {
		$params = ['software' => ['iqrf' => true]];
		$zipManager = new ZipArchiveManager(self::TEMP_ZIP_PATH);
		$this->featureManager->shouldReceive('get')
			->andReturn(['enabled' => false]);
		$this->controllerBackup = new ControllerBackup(self::CONF_PATH, $this->commandManager, $this->featureManager, $this->logger);
		$this->controllerBackup->backup($params, $zipManager);
		foreach (ControllerBackup::WHITELIST as $file) {
			Assert::false($zipManager->exist('controller/' . $file));
		}
		Assert::false($zipManager->exist('controller/'));
	}

	/**
	 * Tests the function to backup with enabled controller feature
	 */
	public function testBackup(): void {
		$params = ['software' => ['iqrf' => true]];
		$files = array_map(function (string $file): string {
			return 'controller/' . $file;
		}, ControllerBackup::WHITELIST);
		$zipManager = new ZipArchiveManager(self::TEMP_ZIP_PATH);
		$this->featureManager->shouldReceive('get')
			->andReturn(['enabled' => true]);
		$this->controllerBackup = new ControllerBackup(self::CONF_PATH, $this->commandManager, $this->featureManager, $this->logger);
		$this->controllerBackup->backup($params, $zipManager);
		Assert::same($files, $zipManager->listFiles());
	}

	/**
	 * Tests the function to restore without controller data
	 */
	public function testRestoreNoController(): void {
		$zipManager = new ZipArchiveManager(self::TEMP_ZIP_PATH);
		$this->featureManager->shouldReceive('get')
			->andReturn(['enabled' => true]);
		$this->controllerBackup = new ControllerBackup(self::TEMP_CONF_PATH, $this->commandManager, $this->featureManager, $this->logger);
		$this->controllerBackup->restore($zipManager);
		foreach (ControllerBackup::WHITELIST as $file) {
			Assert::false(file_exists(self::TEMP_CONF_PATH . $file));
		}
	}

	public function testRestore(): void {
		$zipManager = new ZipArchiveManager(self::ZIP_PATH, ZipArchive::CREATE);
		$this->featureManager->shouldReceive('get')
			->andReturn(['enabled' => true]);
		$this->logger->shouldReceive('log');
		$this->controllerBackup = new ControllerBackup(self::TEMP_CONF_PATH, $this->commandManager, $this->featureManager, $this->logger);
		$this->controllerBackup->restore($zipManager);
		foreach (ControllerBackup::WHITELIST as $file) {
			Assert::true(file_exists(self::TEMP_CONF_PATH . $file));
			Assert::same(FileSystem::read(self::CONF_PATH . $file), FileSystem::read(self::TEMP_CONF_PATH . $file));
		}
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		Environment::lock('controller_backup', TMP_DIR);
		$commandStack = new CommandStack();
		$this->commandManager = new CommandManager(false, $commandStack);
		$this->featureManager = Mockery::mock(FeatureManager::class);
	}

	/**
	 * Test enviornment cleanup
	 */
	protected function tearDown(): void {
		FileSystem::delete(self::TEMP_CONF_PATH);
		parent::tearDown();
	}

}

$test = new ControllerBackupTest();
$test->run();
