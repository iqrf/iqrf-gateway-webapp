<?php

/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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

namespace App\GatewayModule\Models\Backup;

use App\CoreModule\Models\FeatureManager;
use App\CoreModule\Models\PrivilegedFileManager;
use App\CoreModule\Models\ZipArchiveManager;
use Iqrf\CommandExecutor\CommandExecutor;

/**
 * Cloud provisioning backup manager
 */
class CloudProvisioningBackup implements IBackupManager {

	/**
	 * Service name
	 */
	final public const SERVICES = [
		'iqrf-cloud-provisioning',
	];

	/**
	 * @var bool Indicates whether feature is enabled
	 */
	private readonly bool $featureEnabled;

	/**
	 * Constructor
	 * @param CommandExecutor $commandManager Command manager
	 * @param PrivilegedFileManager $fileManager Privileged file manager
	 * @param FeatureManager $featureManager FeatureManager
	 * @param RestoreLogger $restoreLogger Restore logger
	 */
	public function __construct(
		private readonly CommandExecutor $commandManager,
		private readonly PrivilegedFileManager $fileManager,
		FeatureManager $featureManager,
		private readonly RestoreLogger $restoreLogger,
	) {
		$this->featureEnabled = $featureManager->get('iqrfCloudProvisioning')['enabled'];
	}

	/**
	 * Performs Mender backup
	 * @param array<string, array<string, bool>> $params Request parameters
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function backup(array $params, ZipArchiveManager $zipManager): void {
		if (!$params['software']['iqrf'] || !$this->featureEnabled) {
			return;
		}
		foreach ($this->fileManager->listFiles() as $file) {
			$zipManager->addFileFromText('cloudProv/' . $file, $this->fileManager->read($file));
		}
	}

	/**
	 * Performs Mender restore
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function restore(ZipArchiveManager $zipManager): void {
		if (!$zipManager->exist('cloudProv/') || !$this->featureEnabled) {
			return;
		}
		$this->restoreLogger->log('Restoring IQRF Cloud Provisioning configuration.');
		$this->recreateDirectory();
		foreach ($zipManager->listFiles() as $file) {
			if (str_starts_with($file, 'cloudProv/')) {
				$this->fileManager->write(basename($file), $zipManager->openFile($file));
			}
		}
		$this->fixPrivileges();
	}

	/**
	 * Returns service names
	 * @return array<string> Service names
	 */
	public function getServices(): array {
		return $this->featureEnabled ? self::SERVICES : [];
	}

	/**
	 * Recreates directory
	 */
	private function recreateDirectory(): void {
		$user = posix_getpwuid(posix_geteuid());
		$owner = $user['name'] . ':' . posix_getgrgid($user['gid'])['name'];
		$path = escapeshellarg($this->fileManager->getBasePath());
		$this->commandManager->run('rm -rf ' . $path, true);
		$this->commandManager->run('mkdir ' . $path, true);
		$this->commandManager->run('chown -R ' . $owner . ' ' . $path, true);
	}

	/**
	 * Fixes privileges for restored files
	 */
	private function fixPrivileges(): void {
		$this->fileManager->chown(null, 'root', 'root', true);
		$this->fileManager->chmod(null, 0755);
		foreach ($this->fileManager->listFiles() as $file) {
			$this->fileManager->chmod($file, 0644);
		}
	}

}
