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

use App\CoreModule\Models\PrivilegedFileManager;
use App\CoreModule\Models\ZipArchiveManager;
use Iqrf\CommandExecutor\CommandExecutor;
use Nette\Utils\FileSystem;

/**
 * NTP backup manager
 */
class TimesyncdBackup implements IBackupManager {

	/**
	 * List of whitelisted files
	 */
	final public const WHITELIST = [
		'timesyncd.conf',
	];

	/**
	 * Service name
	 */
	final public const SERVICES = [
		'systemd-timesyncd',
	];

	/**
	 * @var string Path to NTP configuration directory
	 */
	private readonly string $path;

	/**
	 * @var string File name
	 */
	private readonly string $file;

	/**
	 * @var PrivilegedFileManager Privileged file manager
	 */
	private readonly PrivilegedFileManager $fileManager;

	/**
	 * Constructor
	 * @param CommandExecutor $commandManager Command manager
	 * @param string $path Path to conf
	 * @param RestoreLogger $restoreLogger Restore logger
	 */
	public function __construct(
		private readonly CommandExecutor $commandManager,
		string $path,
		private readonly RestoreLogger $restoreLogger,
	) {
		$this->path = dirname($path);
		$this->file = basename($path);
		$this->fileManager = new PrivilegedFileManager($this->path, $this->commandManager);
	}

	/**
	 * Performs Timesync backup
	 * @param array<string, array<string, bool>> $params Request parameters
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function backup(array $params, ZipArchiveManager $zipManager): void {
		if (!$params['system']['time']) {
			return;
		}
		$zipManager->addFile($this->path . '/' . $this->file, 'timesyncd/timesyncd.conf');
	}

	/**
	 * Performs Timesync restore
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function restore(ZipArchiveManager $zipManager): void {
		if (!$zipManager->exist('timesyncd/')) {
			return;
		}
		$this->restoreLogger->log('Restoring Timesyncd configuration.');
		$zipManager->extract(self::TMP_PATH, 'timesyncd/timesyncd.conf');
		$this->fileManager->copy($this->file, self::TMP_PATH . 'timesyncd/timesyncd.conf');
		FileSystem::delete(self::TMP_PATH . 'timesyncd');
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
		$this->fileManager->chown($this->file, 'root', 'root');
		$this->fileManager->chmod($this->file, 0644);
	}

}
