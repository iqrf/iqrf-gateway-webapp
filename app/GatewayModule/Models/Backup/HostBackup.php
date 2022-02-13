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
 * Host backup manager
 */
class HostBackup implements IBackupManager {

	/**
	 * List of whitelisted files
	 */
	public const WHITELIST = [
		'hostname',
		'hosts',
	];

	/**
	 * @var string Path to configuration directory
	 */
	private $path;

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
	 * @param string $path Path to configuration directory
	 * @param CommandManager $commandManager Command manager
	 * @param RestoreLogger $restoreLogger Restore logger
	 */
	public function __construct(string $path, CommandManager $commandManager, RestoreLogger $restoreLogger) {
		$this->path = $path;
		$this->commandManager = $commandManager;
		$this->fileManager = new PrivilegedFileManager($this->path, $commandManager);
		$this->restoreLogger = $restoreLogger;
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
		$zipManager->addFile($this->path . 'hostname', 'host/hostname');
		$zipManager->addFile($this->path . 'hosts', 'host/hosts');
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
		$this->fileManager->write('hostname', FileSystem::read(self::TMP_PATH . 'host/hostname'));
		$this->fileManager->write('hosts', FileSystem::read(self::TMP_PATH . 'host/hosts'));
		$this->commandManager->run('rm -rf ' . self::TMP_PATH . 'host');
	}

}
