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
 * Monit backup manager
 */
class MonitBackup implements IBackupManager {

	/**
	 * List of whitelisted files
	 */
	public const WHITELIST = [
		'monitrc',
	];

	/**
	 * Service name
	 */
	public const SERVICE = 'monit';

	/**
	 * Path to Monit configuration directory
	 */
	private const CONF_PATH = '/etc/monit/';

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
	 */
	public function __construct(CommandManager $commandManager, RestoreLogger $restoreLogger) {
		$this->commandManager = $commandManager;
		$this->fileManager = new PrivilegedFileManager(self::CONF_PATH, $commandManager);
		$this->restoreLogger = $restoreLogger;
	}

	/**
	 * Performs Monit backup
	 * @param array<string, array<string, bool>> $params Request parameters
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function backup(array $params, ZipArchiveManager $zipManager): void {
		if (!$params['software']['monit']) {
			return;
		}
		if (!file_exists(self::CONF_PATH)) {
			return;
		}
		$zipManager->addEmptyFolder('monit');
		$zipManager->addFileFromText('monit/monitrc', $this->fileManager->read('monitrc'));
	}

	/**
	 * Performs Monit restore
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function restore(ZipArchiveManager $zipManager): void {
		if (!$zipManager->exist('monit/')) {
			return;
		}
		$this->restoreLogger->log('Restoring Monit configuration.');
		$zipManager->extract(self::TMP_PATH, 'monit/monitrc');
		$this->fileManager->write('monitrc', FileSystem::read(self::TMP_PATH . 'monit/monitrc'));
		$this->commandManager->run('rm -rf ' . self::TMP_PATH . 'monit', true);
		$this->fixMetadata();
	}

	/**
	 * Fixes metadata for restored files
	 */
	private function fixMetadata(): void {
		$this->commandManager->run('chmod 0600 ' . self::CONF_PATH . 'monitrc');
	}

}
