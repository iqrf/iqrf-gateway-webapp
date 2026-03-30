<?php

/**
 * TEST: App\GatewayModule\Models\Backup\HostBackup
 * @covers App\GatewayModule\Models\Backup\HostBackup
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

use App\GatewayModule\Models\Backup\HostBackup;
use Iqrf\FileManager\PrivilegedFileManager;
use Mockery;
use Mockery\MockInterface;
use Tester\Assert;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for backup manager for /etc/hosts and /etc/hostname files
 */
final class HostBackupTest extends BackupManagerTestCase {

	/**
	 * @var PrivilegedFileManager&MockInterface $fileManager File manager
	 */
	private PrivilegedFileManager $fileManager;

	/**
	 * Tests the method for backing up the files
	 */
	public function testBackupDisabled(): void {
		$params = [
			'system' => [
				'hostname' => false,
			],
		];
		$this->zipArchive->shouldNotHaveBeenCalled();
		Assert::noError(function () use ($params): void {
			$this->manager->backup($params, $this->zipArchive);
		});
	}

	/**
	 * Tests the method for backing up the files
	 */
	public function testBackupEnabled(): void {
		$params = [
			'system' => [
				'hostname' => true,
			],
		];
		$this->zipArchive->shouldReceive('addFile')
			->with('/etc/hostname', 'host/hostname')
			->once();
		$this->zipArchive->shouldReceive('addFile')
			->with('/etc/hosts', 'host/hosts')
			->once();
		Assert::noError(function () use ($params): void {
			$this->manager->backup($params, $this->zipArchive);
		});
	}

	/**
	 * Tests the method for restoring the files
	 */
	public function testRestoreDisabled(): void {
		$this->zipArchive->shouldReceive('exist')
			->with('host/')
			->once()
			->andReturn(false);
		Assert::noError(fn () => $this->manager->restore($this->zipArchive));
	}

	/**
	 * Tests the method for getting the service names
	 */
	public function testGetServices(): void {
		Assert::same([], $this->manager->getServices());
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->fileManager = Mockery::mock(PrivilegedFileManager::class);
		$this->manager = new HostBackup(
			fileManager: $this->fileManager,
			restoreLogger: $this->logger,
		);
	}

}

$test = new HostBackupTest();
$test->run();
