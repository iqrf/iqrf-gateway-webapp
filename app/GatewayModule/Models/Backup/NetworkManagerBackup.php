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
 * NetworkManager backup manager
 */
class NetworkManagerBackup implements IBackupManager {

	/**
	 * List of whitelisted files
	 */
	public const WHITELIST = [
		'NetworkManager.conf',
	];

	/**
	 * Service name
	 */
	public const SERVICE = 'NetworkManager';

	/**
	 * Path to NetworkManager configuration directory
	 */
	private const CONF_PATH = '/etc/NetworkManager/';

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
	 * Performs NetworkManager backup
	 * @param array<string, array<string, bool>> $params Request parameters
	 * @param array<string, bool> $services Array of services
	 */
	public function backup(array $params, array &$services): void {
		if (!$params['system']['network']) {
			return;
		}
		$this->zipManager->addFile(self::CONF_PATH . 'NetworkManager.conf', 'nm/NetworkManager.conf');
		$this->zipManager->addEmptyFolder('nm/system-connections');
		$files = $this->fileManager->listFiles();
		foreach ($files as $file) {
			if (strpos($file, DIRECTORY_SEPARATOR . 'system-connections' . DIRECTORY_SEPARATOR) !== false) {
				$name = basename($file);
				$this->zipManager->addFileFromText('nm/system-connections/' . $name, $this->fileManager->read('system-connections/' . $name));
			}
		}
		$services[] = self::SERVICE;
	}

	/**
	 * Performs NetworkManager restore
	 */
	public function restore(): void {
		if (!$this->zipManager->exist('nm/')) {
			return;
		}
		foreach ($this->zipManager->listFiles() as $file) {
			if (strpos($file, 'nm/') === 0) {
				$this->zipManager->extract(self::TMP_PATH, $file);
				if (strpos($file, 'system-connections/') !== false) {
					$this->fileManager->write('system-connections/' . basename($file), FileSystem::read(self::TMP_PATH . $file));
				} else {
					$this->fileManager->write(basename($file), FileSystem::read(self::TMP_PATH . $file));
				}
			}
		}
		$this->setPermissions();
		$this->commandManager->run('rm -rf ' . self::TMP_PATH . 'nm');
	}

	/**
	 * Sets permissions for connection profiles
	 */
	private function setPermissions(): void {
		$this->commandManager->run('chmod 0600 ' . self::CONF_PATH . 'system-connections/*', true);
	}

}
