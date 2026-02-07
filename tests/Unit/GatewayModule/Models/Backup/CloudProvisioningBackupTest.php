<?php

/**
 * TEST: App\GatewayModule\Models\Backup\CloudProvisioningBackup
 * @covers App\GatewayModule\Models\Backup\CloudProvisioningBackup
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

use App\GatewayModule\Models\Backup\CloudProvisioningBackup;
use Iqrf\FileManager\PrivilegedFileManager;
use Mockery;
use Mockery\MockInterface;
use Tester\Assert;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for backup manager for IQRF Cloud provisioning application
 */
final class CloudProvisioningBackupTest extends BackupManagerTestCase {

	/**
	 * @var PrivilegedFileManager&MockInterface $fileManager File manager
	 */
	private PrivilegedFileManager $fileManager;

	/**
	 * Tests the method for getting the service names
	 */
	public function testGetServices(): void {
		Assert::same([], $this->managerDisabled->getServices());
		Assert::same(['iqrf-cloud-provisioning'], $this->managerEnabled->getServices());
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->featureName = 'iqrfCloudProvisioning';
		parent::setUp();
		$this->fileManager = Mockery::mock(PrivilegedFileManager::class);
		$this->managerDisabled = new CloudProvisioningBackup(
			fileManager: $this->fileManager,
			commandManager: $this->commandExecutor,
			featureManager: $this->featureManagerDisabled,
			restoreLogger: $this->logger,
		);
		$this->managerEnabled = new CloudProvisioningBackup(
			fileManager: $this->fileManager,
			commandManager: $this->commandExecutor,
			featureManager: $this->featureManagerEnabled,
			restoreLogger: $this->logger,
		);
	}

}

$test = new CloudProvisioningBackupTest();
$test->run();
