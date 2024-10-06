<?php

/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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
	 * List of whitelisted files
	 */
	final public const WHITELIST = [
		'mender.conf',
		'mender-connect.conf',
	];

	/**
	 * Service names
	 */
	final public const SERVICES = [
		'mender-client',
		'mender-connect',
	];

	/**
	 * @var bool Indicates whether feature is enabled
	 */
	private readonly bool $featureEnabled;

	/**
	 * Constructor
	 * @param PrivilegedFileManager $fileManager Privileged file manager
	 * @param FeatureManager $featureManager FeatureManager
	 * @param RestoreLogger $restoreLogger Restore logger
	 */
	public function __construct(
		private readonly PrivilegedFileManager $fileManager,
		FeatureManager $featureManager,
		private readonly RestoreLogger $restoreLogger,
	) {
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
		if ($this->fileManager->exists('')) {
			$zipManager->addFileFromText('mender/mender.conf', $this->fileManager->read('mender.conf'));
			$zipManager->addFileFromText('mender/mender-connect.conf', $this->fileManager->read('mender-connect.conf'));
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
	 * Returns service names
	 * @return array<string> Service names
	 */
	public function getServices(): array {
		return $this->featureEnabled ? self::SERVICES : [];
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

}
