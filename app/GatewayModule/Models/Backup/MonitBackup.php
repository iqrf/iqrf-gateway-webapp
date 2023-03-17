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
 * Monit backup manager
 */
class MonitBackup implements IBackupManager {

	/**
	 * @var array<string> List of whitelisted files
	 */
	final public const WHITELIST = [
		'monitrc',
	];

	/**
	 * @var array<string> Service name
	 */
	final public const SERVICES = [
		'monit',
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
		$this->featureEnabled = $featureManager->get('monit')['enabled'];
	}

	/**
	 * Performs Monit backup
	 * @param array<string, array<string, bool>> $params Request parameters
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function backup(array $params, ZipArchiveManager $zipManager): void {
		if (!$params['software']['monit'] || !$this->featureEnabled) {
			return;
		}
		if ($this->fileManager->exists('')) {
			$zipManager->addFileFromText('monit/monitrc', $this->fileManager->read('monitrc'));
		}
	}

	/**
	 * Performs Monit restore
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function restore(ZipArchiveManager $zipManager): void {
		if (!$zipManager->exist('monit/') || !$this->featureEnabled) {
			return;
		}
		$this->restoreLogger->log('Restoring Monit configuration.');
		$zipManager->extract(self::TMP_PATH, 'monit/monitrc');
		$this->fileManager->copy('monitrc', self::TMP_PATH . 'monit/monitrc');
		FileSystem::delete(self::TMP_PATH . 'monit');
		$this->fixPrivileges();
	}

	/**
	 * Fixes privileges for restored files
	 */
	private function fixPrivileges(): void {
		$this->fileManager->chown('monitrc', 'root', 'root');
		$this->fileManager->chmod('monitrc', 0600);
	}

	/**
	 * Returns service names
	 * @return array<string> Service names
	 */
	public function getServices(): array {
		return self::SERVICES;
	}

}
