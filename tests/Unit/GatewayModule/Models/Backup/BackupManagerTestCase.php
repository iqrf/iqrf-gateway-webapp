<?php

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

use App\CoreModule\Models\FeatureManager;
use App\CoreModule\Models\ZipArchiveManager;
use App\GatewayModule\Models\Backup\IBackupManager;
use App\GatewayModule\Models\Backup\RestoreLogger;
use Iqrf\CommandExecutor\Tester\Traits\CommandExecutorTestCase;
use Mockery;
use Mockery\MockInterface;
use Tester\TestCase;

abstract class BackupManagerTestCase extends TestCase {

	use CommandExecutorTestCase;

	/**
	 * @var string|null Feature name
	 */
	protected ?string $featureName = null;

	/**
	 * @var FeatureManager&MockInterface $featureManager Feature manager mock with disabled feature
	 */
	protected FeatureManager $featureManagerDisabled;

	/**
	 * @var FeatureManager&MockInterface $featureManagerEnabled Feature manager mock with enabled feature
	 */
	protected FeatureManager $featureManagerEnabled;

	/**
	 * @var RestoreLogger $logger Restore logger
	 */
	protected RestoreLogger $logger;

	/**
	 * @var IBackupManager $managerDisabled Backup manager with disabled feature
	 */
	protected IBackupManager $managerDisabled;

	/**
	 * @var IBackupManager $managerEnabled Backup manager with enabled feature
	 */
	protected IBackupManager $managerEnabled;

	/**
	 * @var IBackupManager $manager Backup manager for tests without feature
	 */
	protected IBackupManager $manager;

	/**
	 * @var ZipArchiveManager&MockInterface $zipArchive ZIP archive manager
	 */
	protected ZipArchiveManager $zipArchive;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->setUpCommandExecutor();
		if ($this->featureName !== null) {
			$this->featureManagerDisabled = Mockery::mock(FeatureManager::class);
			$this->featureManagerDisabled->shouldReceive('isEnabled')
				->with($this->featureName)
				->once()
				->andReturnFalse();
			$this->featureManagerEnabled = Mockery::mock(FeatureManager::class);
			$this->featureManagerEnabled->shouldReceive('isEnabled')
				->with($this->featureName)
				->once()
				->andReturnTrue();
		}
		$this->logger = Mockery::mock(RestoreLogger::class);
		$this->zipArchive = Mockery::mock(ZipArchiveManager::class);
	}

}
