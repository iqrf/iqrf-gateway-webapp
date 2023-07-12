<?php

/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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
 * NetworkManager backup manager
 */
class NetworkManagerBackup implements IBackupManager {

	/**
	 * List of whitelisted files
	 */
	final public const WHITELIST = [
		'NetworkManager.conf',
	];

	/**
	 * Service name
	 */
	final public const SERVICES = [
		'NetworkManager',
	];

	/**
	 * @var bool Indicates whether feature is enabled
	 */
	private readonly bool $featureEnabled;

	/**
	 * Constructor
	 * @param PrivilegedFileManager $fileManager Privileged file manager
	 * @param FeatureManager $featureManager Feature manager
	 * @param RestoreLogger $restoreLogger Restore logger
	 */
	public function __construct(
		private readonly PrivilegedFileManager $fileManager,
		FeatureManager $featureManager,
		private readonly RestoreLogger $restoreLogger,
	) {
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
			if (str_starts_with($file, 'nm/')) {
				$zipManager->extract(self::TMP_PATH, $file);
				if (str_contains($file, 'system-connections/')) {
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
	 * Returns service names
	 * @return array<string> Service names
	 */
	public function getServices(): array {
		return self::SERVICES;
	}

	/**
	 * Fixes privileges for restored files
	 */
	private function fixPrivileges(): void {
		$this->fileManager->chown('NetworkManager.conf', 'root', 'root');
		$this->fileManager->chmod('NetworkManager.conf', 0644);
		foreach ($this->fileManager->listFiles('system-connections') as $connectionFile) {
			$this->fileManager->chown('system-connections/' . $connectionFile, 'root', 'root');
			$this->fileManager->chmod('system-connections/' . $connectionFile, 0600);
		}
	}

}
