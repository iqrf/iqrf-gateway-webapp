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
use App\CoreModule\Models\ZipArchiveManager;
use App\GatewayModule\Models\SshManager;
use Nette\Utils\FileSystem;
use Nette\Utils\Strings;

/**
 * Webapp backup manager
 */
class WebappBackup implements IBackupManager {

	/**
	 * @var array<string> List of whitelisted webapp files
	 */
	public const WHITELIST = [
		'database.db',
		'features.neon',
		'iqrf-repository.neon',
		'smtp.neon',
	];

	/**
	 * @var array<string> List of whitelisted nginx files
	 */
	public const NGINX_WHITELIST = [
		'iqrf-gateway-webapp.localhost',
		'iqrf-gateway-webapp-https.localhost',
		'iqrf-gateway-webapp-iqaros.localhost',
		'iqrf-gateway-webapp-iqaros-https.localhost',
	];

	/**
	 * @var string Path to configuration directory
	 */
	private string $path;

	/**
	 * @var string Path to Webapp database directory
	 */
	private const DB_PATH = '/var/lib/iqrf-gateway-webapp/';

	/**
	 * @var string Path to Webapp nginx configuration directory
	 */
	private const NGINX_PATH = '/etc/iqrf-gateway-webapp/nginx/';

	/**
	 * @var CommandManager Command manager
	 */
	private CommandManager $commandManager;

	/**
	 * @var SshManager SSH manager
	 */
	private SshManager $sshManager;

	/**
	 * @var RestoreLogger Restore logger
	 */
	private RestoreLogger $restoreLogger;

	/**
	 * Constructor
	 * @param string $path Path to configuration directory
	 * @param CommandManager $commandManager Command manager
	 * @param SshManager $sshManager SSH manager
	 * @param RestoreLogger $restoreLogger Restore logger
	 */
	public function __construct(string $path, CommandManager $commandManager, SshManager $sshManager, RestoreLogger $restoreLogger) {
		$this->path = $path;
		$this->commandManager = $commandManager;
		$this->sshManager = $sshManager;
		$this->restoreLogger = $restoreLogger;
	}

	/**
	 * Performs Webapp backup
	 * @param array<string, array<string, bool>> $params Request parameters
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function backup(array $params, ZipArchiveManager $zipManager): void {
		if (!$params['software']['iqrf']) {
			return;
		}
		$zipManager->addFile($this->path . '/features.neon', 'webapp/features.neon');
		$zipManager->addFile($this->path . '/iqrf-repository.neon', 'webapp/iqrf-repository.neon');
		$zipManager->addFile($this->path . '/smtp.neon', 'webapp/smtp.neon');
		$zipManager->addFile(self::DB_PATH . 'database.db', 'webapp/database.db');
		$zipManager->addFolder(self::NGINX_PATH, 'nginx');
	}

	/**
	 * Performs Webapp restore
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function restore(ZipArchiveManager $zipManager): void {
		if (!$zipManager->exist('webapp/') && !$zipManager->exist('nginx/')) {
			return;
		}
		foreach ($zipManager->listFiles() as $file) {
			if (Strings::startsWith($file, 'nginx/') || Strings::startsWith($file, 'webapp/')) {
				$zipManager->extract(self::TMP_PATH, $file);
			}
		}
		$this->restoreLogger->log('Restoring IQRF Gateway Webapp configuration, database and nginx configuration.');
		$this->commandManager->run('cp -p ' . self::TMP_PATH . 'webapp/database.db ' . self::DB_PATH, true);
		FileSystem::delete(self::TMP_PATH . 'webapp/database.db');
		$this->commandManager->run('cp -p ' . self::TMP_PATH . 'webapp/* ' . $this->path, true);
		FileSystem::delete(self::TMP_PATH . 'webapp');
		$this->commandManager->run('cp -p ' . self::TMP_PATH . 'nginx/* ' . self::NGINX_PATH, true);
		FileSystem::delete(self::TMP_PATH . 'nginx');
		$this->sshManager->updateKeysFile();
	}

	/**
	 * Returns service names
	 * @return array<string> Service names
	 */
	public function getServices(): array {
		return [];
	}

}
