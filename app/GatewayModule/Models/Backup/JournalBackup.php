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
 * Systemd journal backup manager
 */
class JournalBackup implements IBackupManager {

	/**
	 * List of whitelisted files
	 */
	public const WHITELIST = [
		'journald.conf',
	];

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
	 * @var ZipArchiveManager ZIP archive manager
	 */
	private $zipManager;

	/**
	 * Constructor
	 * @param string $path Path to NTP configuration directory
	 * @param CommandManager $commandManager Command manager
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function __construct(string $path, CommandManager $commandManager, ZipArchiveManager $zipManager) {
		$this->commandManager = $commandManager;
		$this->path = dirname($path);
		$this->fileManager = new PrivilegedFileManager($this->path, $commandManager);
		$this->zipManager = $zipManager;
	}

	/**
	 * Performs systemd journal backup
	 */
	public function backup(): void {
		$this->zipManager->addFile($this->path . '/journald.conf', 'journal/journald.conf');
	}

	/**
	 * Performs systemd journal restore
	 */
	public function restore(): void {
		$this->zipManager->extract(self::TMP_PATH, 'journal/journald.conf');
		$this->fileManager->write($this->path . 'journald.conf', FileSystem::read(self::TMP_PATH . 'journal/journald.conf'));
		$this->commandManager->run('rm -rf ' . self::TMP_PATH . 'journal');
	}

}
