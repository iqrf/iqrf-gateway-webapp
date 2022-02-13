<?php

/**
 * TEST: App\GatewayModule\Models\Backup\HostBackup
 * @covers App\GatewayModule\Models\Backup\HostBackup
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
use App\CoreModule\Models\ZipArchiveManager;
use App\GatewayModule\Models\Backup\HostBackup;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tester\Environment;
use Tests\Toolkit\TestCases\BackupTestCase;
use ZipArchive;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for host backup manager
 */
final class HostBackupTest extends BackupTestCase {

	/**
	 * Host configuration directory path
	 */
	private const HOST_PATH = TESTER_DIR . '/data/backup/host/';

	/**
	 * Temporary host configuration directory path
	 */
	private const TEMP_HOST_PATH = TMP_DIR . '/backup/host/';

	/**
	 * @var HostBackup Host backup manager
	 */
	private $hostBackup;

	/**
	 * @var HostBackup Temporary host backup manager
	 */
	private $hostBackupTemp;

	/**
	 * Tests the function to backup with hostname not being requested
	 */
	public function testBackupNoHost(): void {
		$params = ['system' => ['hostname' => false]];
		$zipManager = new ZipArchiveManager(self::TEMP_ZIP_PATH);
		$this->hostBackup->backup($params, $zipManager);
		foreach (HostBackup::WHITELIST as $file) {
			Assert::false($zipManager->exist('host/' . $file));
		}
		Assert::false($zipManager->exist('host/'));
	}

	/**
	 * Tests the function to backup with hostname requested
	 */
	public function testBackup(): void {
		$params = ['system' => ['hostname' => true]];
		$files = array_map(function (string $file): string {
			return 'host/' . $file;
		}, HostBackup::WHITELIST);
		$zipManager = new ZipArchiveManager(self::TEMP_ZIP_PATH);
		$this->hostBackup->backup($params, $zipManager);
		Assert::same($files, $zipManager->listFiles());
	}

	/**
	 * Tests the function to restore without hostname data
	 */
	public function testRestoreNoHost(): void {
		$zipManager = new ZipArchiveManager(self::TEMP_ZIP_PATH);
		$this->hostBackupTemp->restore($zipManager);
		foreach (HostBackup::WHITELIST as $file) {
			Assert::false(file_exists(self::TEMP_HOST_PATH . $file));
		}
	}

	/**
	 * Tests the function to restore with hostname data
	 */
	public function testRestore(): void {
		$zipManager = new ZipArchiveManager(self::ZIP_PATH, ZipArchive::CREATE);
		$this->logger->shouldReceive('log');
		$this->hostBackupTemp->restore($zipManager);
		foreach (HostBackup::WHITELIST as $file) {
			Assert::true(file_exists(self::TEMP_HOST_PATH . $file));
			Assert::same(FileSystem::read(self::HOST_PATH . $file), FileSystem::read(self::TEMP_HOST_PATH . $file));
		}
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		Environment::lock('host_backup', TMP_DIR);
		$commandStack = new CommandStack();
		$commandManager = new CommandManager(false, $commandStack);
		$this->hostBackup = new HostBackup(self::HOST_PATH, $commandManager, $this->logger);
		$this->hostBackupTemp = new HostBackup(self::TEMP_HOST_PATH, $commandManager, $this->logger);
	}

	/**
	 * Test environment teardown
	 */
	protected function tearDown(): void {
		FileSystem::delete(self::TEMP_HOST_PATH);
		parent::tearDown();
	}

}

$test = new HostBackupTest();
$test->run();
