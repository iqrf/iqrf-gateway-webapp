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
	public const WHITELIST = [
		'mender.conf',
		'mender-connect.conf',
	];

	/**
	 * Service names
	 */
	public const SERVICE = [
		'mender-client',
		'mender-connect',
	];

	/**
	 * Path to Mender configuration directory
	 */
	private const CONF_PATH = '/etc/mender/';

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
	 * @param RestoreLogger $restoreLogger Restore logger
	 */
	public function __construct(CommandManager $commandManager, RestoreLogger $restoreLogger) {
		$this->commandManager = $commandManager;
		$this->fileManager = new PrivilegedFileManager(self::CONF_PATH, $commandManager);
		$this->restoreLogger = $restoreLogger;
	}

	/**
	 * Performs Mender backup
	 * @param array<string, array<string, bool>> $params Request parameters
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function backup(array $params, ZipArchiveManager $zipManager): void {
		if (!$params['software']['mender']) {
			return;
		}
		$zipManager->addFile(self::CONF_PATH . 'mender.conf', 'mender/mender.conf');
		$zipManager->addFile(self::CONF_PATH . 'mender-connect.conf', 'mender/mender-connect.conf');
	}

	/**
	 * Performs Mender restore
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function restore(ZipArchiveManager $zipManager): void {
		if (!$zipManager->exist('mender/')) {
			return;
		}
		$this->restoreLogger->log('Restoring Mender configuration.');
		$zipManager->extract(self::TMP_PATH, 'mender/mender.conf');
		$zipManager->extract(self::TMP_PATH, 'mender/mender-connect.conf');
		$this->fileManager->write('mender.conf', FileSystem::read(self::TMP_PATH . 'mender/mender.conf'));
		$this->fileManager->write('mender-connect.conf', FileSystem::read(self::TMP_PATH . 'mender/mender-connect.conf'));
		$this->commandManager->run('rm -rf ' . self::TMP_PATH . 'mender');
	}

}
