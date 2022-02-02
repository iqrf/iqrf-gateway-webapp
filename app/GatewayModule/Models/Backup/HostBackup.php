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
class HostBackup {

	/**
	 * List of whitelisted files
	 */
	public const WHITELIST = [
		'hostname',
		'hosts',
	];

	public const TMP_PATH = '/tmp/backup/';

	/**
	 * Path to configuration directory
	 */
	private const CONF_PATH = '/etc/';

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * @var PrivilegedFileManager Privileged file manager
	 */
	private $fileManager;

	/**
	 * @var ZipArchiveManager ZIP archive manager
	 */
	private $zipManager;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function __construct(CommandManager $commandManager, ZipArchiveManager $zipManager) {
		$this->commandManager = $commandManager;
		$this->fileManager = new PrivilegedFileManager(self::CONF_PATH, $commandManager);
		$this->zipManager = $zipManager;
	}

	/**
	 * Performs host backup
	 * @param array<string, array<string, bool>> $params Request parameters
	 * @param array<string, bool> $services Array of services
	 */
	public function backup(array $params, ?array &$services = null): void {
		if (!$params['system']['hostname']) {
			return;
		}
		$this->zipManager->addFile(self::CONF_PATH . 'hostname', 'host/hostname');
		$this->zipManager->addFile(self::CONF_PATH . 'hosts', 'host/hosts');
	}

	/**
	 * Performs host restore
	 */
	public function restore(): void {
		if (!$this->zipManager->exist('host/')) {
			return;
		}
		$this->zipManager->extract(self::TMP_PATH, 'host/hostname');
		$this->zipManager->extract(self::TMP_PATH, 'host/hosts');
		$this->fileManager->write('hostname', FileSystem::read(self::TMP_PATH . 'host/hostname'));
		$this->fileManager->write('hosts', FileSystem::read(self::TMP_PATH . 'host/hosts'));
		$this->commandManager->run('rm -rf ' . self::TMP_PATH . 'host');
	}

}
