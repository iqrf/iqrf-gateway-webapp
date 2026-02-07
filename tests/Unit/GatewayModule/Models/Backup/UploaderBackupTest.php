<?php

/**
 * TEST: App\GatewayModule\Models\Backup\UploaderBackup
 * @covers App\GatewayModule\Models\Backup\UploaderBackup
 * @phpVersion >= 8.2
 * @testCase
 */
/**
 * Copyright 2017-2026 IQRF Tech s.r.o.
 * Copyright 2019-2026 MICRORISC s.r.o.
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

namespace Tests\Unit\GatewayModule\Models\Backup;

use App\GatewayModule\Models\Backup\UploaderBackup;
use Iqrf\FileManager\FileManager;
use Mockery;
use Mockery\MockInterface;
use Tester\Assert;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for backup manager for IQRF Gateway Uploader application
 */
final class UploaderBackupTest extends BackupManagerTestCase {

	/**
	 * @var FileManager&MockInterface $fileManager File manager
	 */
	private FileManager $fileManager;

	/**
	 * Tests the method for getting the service names
	 */
	public function testGetServices(): void {
		Assert::same([], $this->managerDisabled->getServices());
		Assert::same([], $this->managerEnabled->getServices());
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->featureName = 'trUpload';
		parent::setUp();
		$this->fileManager = Mockery::mock(FileManager::class);
		$this->managerDisabled = new UploaderBackup(
			fileManager: $this->fileManager,
			commandManager: $this->commandExecutor,
			featureManager: $this->featureManagerDisabled,
			restoreLogger: $this->logger,
		);
		$this->managerEnabled = new UploaderBackup(
			fileManager: $this->fileManager,
			commandManager: $this->commandExecutor,
			featureManager: $this->featureManagerEnabled,
			restoreLogger: $this->logger,
		);
	}

}

$test = new UploaderBackupTest();
$test->run();
