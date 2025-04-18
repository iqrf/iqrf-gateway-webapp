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

use App\CoreModule\Models\ZipArchiveManager;
use Iqrf\FileManager\PrivilegedFileManager;
use Nette\Utils\FileSystem;

/**
 * Host backup manager
 */
class HostBackup implements IBackupManager {

	/**
	 * List of whitelisted files
	 */
	final public const WHITELIST = [
		'hostname',
		'hosts',
	];

	/**
	 * Path to configuration directory
	 */
	private const CONF_PATH = '/etc/';

	/**
	 * Constructor
	 * @param PrivilegedFileManager $fileManager Privileged file manager
	 * @param RestoreLogger $restoreLogger Restore logger
	 */
	public function __construct(
		private readonly PrivilegedFileManager $fileManager,
		private readonly RestoreLogger $restoreLogger,
	) {
	}

	/**
	 * Performs host backup
	 * @param array<string, array<string, bool>> $params Request parameters
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function backup(array $params, ZipArchiveManager $zipManager): void {
		if (!$params['system']['hostname']) {
			return;
		}
		$zipManager->addFile(self::CONF_PATH . 'hostname', 'host/hostname');
		$zipManager->addFile(self::CONF_PATH . 'hosts', 'host/hosts');
	}

	/**
	 * Performs host restore
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function restore(ZipArchiveManager $zipManager): void {
		if (!$zipManager->exist('host/')) {
			return;
		}
		$this->restoreLogger->log('Restoring gateway host.');
		$zipManager->extract(self::TMP_PATH, 'host/hostname');
		$zipManager->extract(self::TMP_PATH, 'host/hosts');
		$this->fileManager->copy('hostname', self::TMP_PATH . 'host/hostname');
		$this->fileManager->copy('hosts', self::TMP_PATH . 'host/hosts');
		FileSystem::delete(self::TMP_PATH . 'host');
		$this->fixPrivileges();
	}

	/**
	 * Returns service names
	 * @return array<string> Service names
	 */
	public function getServices(): array {
		return [];
	}

	/**
	 * Fixes privileges for restored files
	 */
	private function fixPrivileges(): void {
		foreach (self::WHITELIST as $file) {
			$this->fileManager->chown($file, 'root', 'root');
			$this->fileManager->chmod($file, 0644);
		}
	}

}
