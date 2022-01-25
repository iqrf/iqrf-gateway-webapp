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
 * Pixla backup manager
 */
class PixlaBackup implements IBackupManager {

	/**
	 * Path to Pixla configuration directory
	 */
	private const CONF_PATH = '/etc/gwman/';

	/**
	 * List of whitelisted files
	 */
	public const WHITELIST = [
		'customer_id',
		'gw_id',
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
	 * Performs Pixla backup
	 */
	public function backup(): void {
		$this->zipManager->addFolder(self::CONF_PATH, 'pixla');
	}

	/**
	 * Performs Pixla restore
	 */
	public function restore(): void {
		$this->zipManager->extract(self::TMP_PATH, 'pixla/customer_id');
		$this->zipManager->extract(self::TMP_PATH, 'pixla/gw_id');
		$this->fileManager->write('customer_id', FileSystem::read(self::TMP_PATH . 'pixla/customer_id'));
		$this->fileManager->write('gw_id', FileSystem::read(self::TMP_PATH . 'pixla/gw_id'));
		$this->commandManager->run('rm -rf ' . self::TMP_PATH . 'pixla', true);
	}

}
