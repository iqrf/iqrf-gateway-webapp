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
use Nette\Utils\Strings;

/**
 * NetworkManager backup manager
 */
class NetworkManagerBackup implements IBackupManager {

	/**
	 * @var array<string> List of whitelisted files
	 */
	public const WHITELIST = [
		'NetworkManager.conf',
	];

	/**
	 * @var array<string> Service name
	 */
	public const SERVICES = [
		'NetworkManager',
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
	 * @param FeatureManager $featureManager Feature manager
	 * @param RestoreLogger $restoreLogger Restore logger
	 */
	public function __construct(PrivilegedFileManager $fileManager, FeatureManager $featureManager, RestoreLogger $restoreLogger) {
		$this->fileManager = $fileManager;
		$this->restoreLogger = $restoreLogger;
		$this->featureEnabled = $featureManager->get('networkManager')['enabled'];
	}

	/**
	 * Performs NetworkManager backup
	 * @param array<string, array<string, bool>> $params Request parameters
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function backup(array $params, ZipArchiveManager $zipManager): void {
		if (!$params['system']['network'] || !$this->featureEnabled) {
			return;
		}
		if ($this->fileManager->exists('')) {
			$zipManager->addFile($this->fileManager->getBasePath() . '/NetworkManager.conf', 'nm/NetworkManager.conf');
			foreach ($this->fileManager->listFiles('system-connections') as $connectionFile) {
				$zipManager->addFileFromText('nm/system-connections/' . $connectionFile, $this->fileManager->read('system-connections/' . $connectionFile));
			}
		}
	}

	/**
	 * Performs NetworkManager restore
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function restore(ZipArchiveManager $zipManager): void {
		if (!$zipManager->exist('nm/') || !$this->featureEnabled) {
			return;
		}
		$this->restoreLogger->log('Restoring NetworkManager configuration and connection profiles.');
		foreach ($zipManager->listFiles() as $file) {
			if (Strings::startsWith($file, 'nm/')) {
				$zipManager->extract(self::TMP_PATH, $file);
				if (Strings::contains($file, 'system-connections/')) {
					$this->fileManager->copy('system-connections/' . basename($file), self::TMP_PATH . $file);
				} else {
					$this->fileManager->copy(basename($file), self::TMP_PATH . $file);
				}
			}
		}
		FileSystem::delete(self::TMP_PATH . 'nm');
		$this->fixPrivileges();
	}

	/**
	 * Fixes privileges for restored files
	 */
	private function fixPrivileges(): void {
		$this->fileManager->chown('NetworkManager.conf', 'root', 'root');
		$this->fileManager->chmod('NetworkManager.conf', 0644);
		foreach ($this->fileManager->listFiles('system-connections') as $connectionFile) {
			$this->fileManager->chown($connectionFile, 'root', 'root');
			$this->fileManager->chmod($connectionFile, 0600);
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
