<?php

/**
 * TEST: App\GatewayModule\Models\Backup\SshBackup
 * @covers App\GatewayModule\Models\Backup\SshBackup
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

use App\GatewayModule\Models\Backup\SshBackup;
use Tester\Assert;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for backup manager for SSH daemon
 */
final class SshBackupTest extends BackupManagerTestCase {

	/**
	 * Tests the method for backing up the files
	 */
	public function testBackup(): void {
		$params = [];
		$this->zipArchive->shouldNotHaveBeenCalled();
		Assert::noError(function () use ($params): void {
			$this->managerDisabled->backup($params, $this->zipArchive);
		});
		Assert::noError(function () use ($params): void {
			$this->managerEnabled->backup($params, $this->zipArchive);
		});
	}

	/**
	 * Tests the method for restoring the files
	 */
	public function testRestore(): void {
		$this->zipArchive->shouldNotHaveBeenCalled();
		Assert::noError(fn () => $this->managerDisabled->restore($this->zipArchive));
		Assert::noError(fn () => $this->managerEnabled->restore($this->zipArchive));
	}

	/**
	 * Tests the method for getting the service names
	 */
	public function testGetServices(): void {
		Assert::same([], $this->managerDisabled->getServices());
		Assert::same(['ssh'], $this->managerEnabled->getServices());
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->featureName = 'ssh';
		parent::setUp();
		$this->managerDisabled = new SshBackup(
			featureManager: $this->featureManagerDisabled,
		);
		$this->managerEnabled = new SshBackup(
			featureManager: $this->featureManagerEnabled,
		);
	}

}

$test = new SshBackupTest();
$test->run();
