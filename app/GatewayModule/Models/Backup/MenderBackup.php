<?php

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

namespace App\GatewayModule\Models\Backup;

use App\CoreModule\Models\FeatureManager;
use App\CoreModule\Models\PrivilegedFileManager;
use App\CoreModule\Models\ZipArchiveManager;
use Nette\Utils\FileSystem;

/**
 * Mender backup manager
 */
class MenderBackup implements IBackupManager {

	/**
	 * @var array<string> List of whitelisted files
	 */
	public const WHITELIST = [
		'mender.conf',
		'mender-connect.conf',
	];

	/**
	 * @var array<string> Service names
	 */
	public const SERVICES = [
		'mender-client',
		'mender-connect',
	];

	/**
	 * @var bool Indicates whether feature is enabled
	 */
	private bool $featureEnabled;

	/**
	 * @var PrivilegedFileManager Privileged file manager
	 */
	private PrivilegedFileManager $fileManager;

	/**
	 * @var RestoreLogger Restore logger
	 */
	private RestoreLogger $restoreLogger;

	/**
	 * Constructor
	 * @param PrivilegedFileManager $fileManager Privileged file manager
	 * @param FeatureManager $featureManager FeatureManager
	 * @param RestoreLogger $restoreLogger Restore logger
	 */
	public function __construct(PrivilegedFileManager $fileManager, FeatureManager $featureManager, RestoreLogger $restoreLogger) {
		$this->fileManager = $fileManager;
		$this->restoreLogger = $restoreLogger;
		$this->featureEnabled = $featureManager->get('mender')['enabled'];
	}

	/**
	 * Performs Mender backup
	 * @param array<string, array<string, bool>> $params Request parameters
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function backup(array $params, ZipArchiveManager $zipManager): void {
		if (!$params['software']['mender'] || !$this->featureEnabled) {
			return;
		}
		$path = $this->fileManager->getBasePath();
		if ($this->fileManager->exists('')) {
			$zipManager->addFile($path . 'mender.conf', 'mender/mender.conf');
			$zipManager->addFile($path . 'mender-connect.conf', 'mender/mender-connect.conf');
		}
	}

	/**
	 * Performs Mender restore
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function restore(ZipArchiveManager $zipManager): void {
		if (!$zipManager->exist('mender/') || !$this->featureEnabled) {
			return;
		}
		$this->restoreLogger->log('Restoring Mender configuration.');
		$zipManager->extract(self::TMP_PATH, 'mender/mender.conf');
		$zipManager->extract(self::TMP_PATH, 'mender/mender-connect.conf');
		$this->fileManager->copy('mender.conf', self::TMP_PATH . 'mender/mender.conf');
		$this->fileManager->copy('mender-connect.conf', self::TMP_PATH . 'mender/mender-connect.conf');
		FileSystem::delete(self::TMP_PATH . 'mender');
		$this->fixPrivileges();
	}

	/**
	 * Fixes privileges for restored files
	 */
	private function fixPrivileges(): void {
		foreach (self::WHITELIST as $file) {
			$this->fileManager->chown($file, 'root', 'root');
			$this->fileManager->chmod($file, 0600);
		}
	}

	/**
	 * Returns service names
	 * @return array<string> Service names
	 */
	public function getServices(): array {
		return self::SERVICES;
	}

}
