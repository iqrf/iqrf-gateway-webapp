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

use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\FeatureManager;
use App\CoreModule\Models\PrivilegedFileManager;
use App\CoreModule\Models\ZipArchiveManager;
use Nette\Utils\FileSystem;

/**
 * Systemd journal backup manager
 */
class JournalBackup implements IBackupManager {

	/**
	 * @var array<string> Whitelisted files
	 */
	public const WHITELIST = [
		'journald.conf',
	];

	/**
	 * @var array<string> Service name
	 */
	public const SERVICES = [
		'systemd-journald',
	];

	/**
	 * @var string Path to NTP configuration directory
	 */
	private string $path;

	/**
	 * @var string File name
	 */
	private string $file;

	/**
	 * @var CommandManager Command manager
	 */
	private CommandManager $commandManager;

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
	 * @param CommandManager $commandManager Command manager
	 * @param FeatureManager $featureManager Feature manager
	 * @param RestoreLogger $restoreLogger Restore logger
	 */
	public function __construct(CommandManager $commandManager, FeatureManager $featureManager, RestoreLogger $restoreLogger) {
		$this->commandManager = $commandManager;
		$this->restoreLogger = $restoreLogger;
		$feature = $featureManager->get('journal');
		$this->path = dirname($feature['path']);
		$this->file = basename($feature['path']);
		$this->featureEnabled = $feature['enabled'];
		$this->fileManager = new PrivilegedFileManager($this->path, $this->commandManager);
	}

	/**
	 * Performs systemd journal backup
	 * @param array<string, array<string, bool>> $params Request parameters
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function backup(array $params, ZipArchiveManager $zipManager): void {
		if (!$params['system']['journal'] || !$this->featureEnabled) {
			return;
		}
		$zipManager->addFile($this->path . '/' . $this->file, 'journal/journald.conf');
	}

	/**
	 * Performs systemd journal restore
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function restore(ZipArchiveManager $zipManager): void {
		if (!$zipManager->exist('journal/') || !$this->featureEnabled) {
			return;
		}
		$this->restoreLogger->log('Restoring systemd-journal configuration.');
		$zipManager->extract(self::TMP_PATH, 'journal/journald.conf');
		$this->fileManager->copy($this->file, self::TMP_PATH . 'journal/journald.conf');
		FileSystem::delete(self::TMP_PATH . 'journal');
		$this->fixPrivileges();
	}

	/**
	 * Fixes privileges for restored files
	 */
	private function fixPrivileges(): void {
		$this->fileManager->chown($this->file, 'root', 'root');
		$this->fileManager->chmod($this->file, 0644);
	}

	/**
	 * Returns service names
	 * @return array<string> Service names
	 */
	public function getServices(): array {
		return self::SERVICES;
	}

}
