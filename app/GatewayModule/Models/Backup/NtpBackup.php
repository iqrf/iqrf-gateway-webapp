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
 * NTP backup manager
 */
class NtpBackup implements IBackupManager {

	/**
	 * List of whitelisted files
	 */
	public const WHITELIST = [
		'timesyncd.conf',
	];

	/**
	 * Service name
	 */
	public const SERVICE = 'systemd-timesyncd';

	/**
	 * @var string Path to NTP configuration directory
	 */
	private $path;

	/**
	 * @var string File name
	 */
	private $file;

	/**
	 * @var bool Indicates whether feature is enabled
	 */
	private $featureEnabled;

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * @var PrivilegedFileManager Privileged file manager
	 */
	private $fileManager;

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
		$this->restoreLogger = $restoreLogger;
		$feature = $featureManager->get('ntp');
		$this->path = dirname($feature['path']);
		$this->file = basename($feature['path']);
		$this->featureEnabled = $feature['enabled'];
		$this->fileManager = new PrivilegedFileManager($this->path, $commandManager);
	}

	/**
	 * Performs Timesync backup
	 * @param array<string, array<string, bool>> $params Request parameters
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function backup(array $params, ZipArchiveManager $zipManager): void {
		if (!$params['system']['time'] || !$this->featureEnabled) {
			return;
		}
		$zipManager->addFile($this->path . '/' . $this->file, 'timesync/timesyncd.conf');
	}

	/**
	 * Performs Timesync restore
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function restore(ZipArchiveManager $zipManager): void {
		if (!$zipManager->exist('timesync/') || !$this->featureEnabled) {
			return;
		}
		$this->restoreLogger->log('Restoring Timesyncd configuration.');
		$zipManager->extract(self::TMP_PATH, 'timesync/timesyncd.conf');
		$this->fileManager->write($this->file, FileSystem::read(self::TMP_PATH . 'timesync/timesyncd.conf'));
		$this->commandManager->run('rm -rf ' . self::TMP_PATH . 'timesync');
	}

}
