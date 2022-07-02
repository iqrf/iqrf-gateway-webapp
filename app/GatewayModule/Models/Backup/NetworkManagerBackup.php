<?php

/**
 * Copyright 2017-2022 IQRF Tech s.r.o.
 * Copyright 2019-2022 MICRORISC s.r.o.
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
use Nette\Utils\Strings;

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
	public const SERVICES = [
		'NetworkManager',
	];

	/**
	 * Path to NetworkManager configuration directory
	 */
	private const CONF_PATH = '/etc/NetworkManager/';

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
		$this->fileManager = new PrivilegedFileManager(self::CONF_PATH, $commandManager);
		$this->restoreLogger = $restoreLogger;
		$this->featureEnabled = $featureManager->get('networkManager')['enabled'];
	}

	/**
	 * Performs NetworkManager backup
	 * @param array<string, array<string, bool>> $params Request parameters
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function backup(array $params, ZipArchiveManager $zipManager): void {
		if (!$params['system']['network'] || !$this->featureEnabled) {
			return;
		}
		if (file_exists(self::CONF_PATH)) {
			$zipManager->addFile(self::CONF_PATH . 'NetworkManager.conf', 'nm/NetworkManager.conf');
			$zipManager->addEmptyFolder('nm/system-connections');
			$files = $this->fileManager->listFiles();
			foreach ($files as $file) {
				if (Strings::contains($file, DIRECTORY_SEPARATOR . 'system-connections' . DIRECTORY_SEPARATOR)) {
					$name = basename($file);
					$zipManager->addFileFromText('nm/system-connections/' . $name, $this->fileManager->read('system-connections/' . $name));
				}
			}
		}
	}

	/**
	 * Performs NetworkManager restore
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function restore(ZipArchiveManager $zipManager): void {
		if (!$zipManager->exist('nm/') || !$this->featureEnabled) {
			return;
		}
		$this->restoreLogger->log('Restoring NetworkManager configuration and connection profiles.');
		foreach ($zipManager->listFiles() as $file) {
			if (Strings::startsWith($file, 'nm/')) {
				$zipManager->extract(self::TMP_PATH, $file);
				if (Strings::contains($file, 'system-connections/')) {
					$this->fileManager->copy('system-connections/' . basename($file), self::TMP_PATH . $file);
				} else {
					$this->fileManager->copy(basename($file), self::TMP_PATH . $file);
				}
			}
		}
		FileSystem::delete(self::TMP_PATH . 'nm');
		$this->fixPrivileges();
	}

	/**
	 * Fixes privileges for restored files
	 */
	private function fixPrivileges(): void {
		$this->commandManager->run('chown root:root ' . self::CONF_PATH . 'NetworkManager.conf', true);
		$this->commandManager->run('chmod 0644 ' . self::CONF_PATH . 'NetworkManager.conf', true);
		$this->commandManager->run('chown root:root ' . self::CONF_PATH . 'system-connections/*', true);
		$this->commandManager->run('chmod 0600 ' . self::CONF_PATH . 'system-connections/*', true);
	}

	/**
	 * Returns service names
	 * @return array<string> Service names
	 */
	public function getServices(): array {
		return self::SERVICES;
	}

}
