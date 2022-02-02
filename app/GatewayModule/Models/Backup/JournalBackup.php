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
	 * Whitelisted files
	 */
	public const WHITELIST = [
		'journald.conf',
	];

	/**
	 * Service name
	 */
	public const SERVICE = 'systemd-timesyncd';

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * @var PrivilegedFileManager Privileged file manager
	 */
	private $fileManager;

	/**
	 * @var string Path to NTP configuration directory
	 */
	private $path;

	/**
	 * @var string File name
	 */
	private $file;

	/**
	 * @var RestoreLogger Restore logger
	 */
	private $restoreLogger;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 * @param FeatureManager $featureManager Feature manager
	 * @param RestoreLogger $restoreLogger Restore logger
	 */
	public function __construct(CommandManager $commandManager, FeatureManager $featureManager, RestoreLogger $restoreLogger) {
		$this->commandManager = $commandManager;
		$path = $featureManager->get('systemdJournal')['path'];
		$this->path = dirname($path);
		$this->file = basename($path);
		$this->fileManager = new PrivilegedFileManager($this->path, $commandManager);
		$this->restoreLogger = $restoreLogger;
	}

	/**
	 * Performs systemd journal backup
	 * @param array<string, array<string, bool>> $params Request parameters
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function backup(array $params, ZipArchiveManager $zipManager): void {
		if (!$params['system']['journal']) {
			return;
		}
		$zipManager->addFile($this->path . '/' . $this->file, 'journal/journald.conf');
	}

	/**
	 * Performs systemd journal restore
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function restore(ZipArchiveManager $zipManager): void {
		if (!$zipManager->exist('journal/')) {
			return;
		}
		$this->restoreLogger->log('Restoring systemd-journal configuration.');
		$zipManager->extract(self::TMP_PATH, 'journal/journald.conf');
		$this->fileManager->write($this->file, FileSystem::read(self::TMP_PATH . 'journal/journald.conf'));
		$this->commandManager->run('rm -rf ' . self::TMP_PATH . 'journal');
	}

}
