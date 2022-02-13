<?php

/**
 * TEST: App\GatewayModule\Models\BackupManager
 * @covers App\GatewayModule\Models\BackupManager
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

namespace Tests\Integration\GatewayModule\Models;

use App\ConfigModule\Models\ComponentSchemaManager;
use App\CoreModule\Entities\CommandStack;
use App\CoreModule\Models\CommandManager;
use App\GatewayModule\Models\BackupManager;
use App\GatewayModule\Models\PowerManager;
use App\GatewayModule\Models\Utils\GatewayInfoUtil;
use App\ServiceModule\Models\ServiceManager;
use Mockery;
use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for backup manager
 */
final class BackupManagerTest extends TestCase {

	/**
	 * Path to temporary backup and restore directory
	 */
	private const TMP_BACKUP_PATH = TMP_DIR . '/backup_restore/';

	/**
	 * Path to a directory with gateway file
	 */
	private const GATEWAY_UTIL_PATH = TESTER_DIR . '/data/gateway/';

	/**
	 * Path to a directory with daemon components JSON schemas
	 */
	private const SCHEMA_PATH = TESTER_DIR . '/data/cfgSchemas/';

	/**
	 * Backup parameters
	 */
	private const BACKUP_PARAMS = [
		'software' => [
			'iqrf' => false,
			'mender' => false,
			'monit' => false,
			'pixla' => false,
		],
		'system' => [
			'hostname' => false,
			'journal' => false,
			'network' => false,
			'time' => false,
		],
	];

	/**
	 * @var BackupManager $backupManager Backup manager
	 */
	private $backupManager;

	/**
	 * @var ServiceManager $serviceManager Service manager
	 */
	private $serviceManager;

	/**
	 * Tests the fuction to backup gateway
	 */
	public function testBackup(): void {
		Assert::noError(function (): void {
			$this->backupManager->backup(self::BACKUP_PARAMS);
		});
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		Environment::lock('backup_restore', TMP_DIR);
		$commandStack = new CommandStack();
		$commandManager = new CommandManager(false, $commandStack);
		$commandManagerMock = Mockery::mock(CommandManager::class, [false, $commandStack])->makePartial();
		$powerManager = Mockery::mock(PowerManager::class);
		$powerManager->shouldReceive('reboot');
		$schemaManager = new ComponentSchemaManager(self::SCHEMA_PATH, $commandManager);
		$this->serviceManager = Mockery::mock(ServiceManager::class);
		$this->serviceManager->shouldReceive('isEnabled')
			->atLeast()->times(2);
		$commandStack = new CommandStack();
		$commandManager = new CommandManager(false, $commandStack);
		$gwInfo = new GatewayInfoUtil(self::GATEWAY_UTIL_PATH, $commandManager);
		$this->backupManager = new BackupManager(self::TMP_BACKUP_PATH, [], $commandManagerMock, $powerManager, $schemaManager, $this->serviceManager, $gwInfo);
	}

}

$test = new BackupManagerTest();
$test->run();
