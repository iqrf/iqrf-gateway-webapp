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

/**
 * Webapp backup manager
 */
class WebappBackup implements IBackupManager {

	/**
	 * Path to Webapp configuration directory
	 */
	private const CONF_PATH = __DIR__ . '/../../../config/';

	/**
	 * Path to Webapp database directory
	 */
	private const DB_PATH = '/var/lib/iqrf-gateway-webapp/';

	/**
	 * Path to Webapp nginx configuration directory
	 */
	private const NGINX_PATH = '/etc/iqrf-gateway-webapp/nginx/';

	/**
	 * List of whitelisted webapp files
	 */
	public const WHITELIST = [
		'database.db',
		'features.neon',
		'iqrf-repository.neon',
		'smtp.neon',
	];

	/**
	 * List of whitelisted nginx files
	 */
	public const NGINX_WHITELIST = [
		'iqrf-gateway-webapp.localhost',
		'iqrf-gateway-webapp-https.localhost',
		'iqrf-gateway-webapp-iqaros.localhost',
		'iqrf-gateway-webapp-iqaros-https.localhost',
	];

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

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
		$this->zipManager = $zipManager;
	}

	/**
	 * Performs Webapp backup
	 */
	public function backup(): void {
		$this->zipManager->addFile(self::CONF_PATH . 'features.neon', 'webapp/features.neon');
		$this->zipManager->addFile(self::CONF_PATH . 'iqrf-repository.neon', 'webapp/iqrf-repository.neon');
		$this->zipManager->addFile(self::CONF_PATH . 'smtp.neon', 'webapp/smtp.neon');
		$this->zipManager->addFile(self::DB_PATH . 'database.db', 'webapp/database.db');
		$this->zipManager->addFolder(self::NGINX_PATH, 'nginx');
	}

	/**
	 * Performs Webapp restore
	 */
	public function restore(): void {
		foreach ($this->zipManager->listFiles() as $file) {
			if (strpos($file, 'nginx/') === 0 || strpos($file, 'webapp/') === 0) {
				$this->zipManager->extract(self::TMP_PATH, $file);
			}
		}
		$this->commandManager->run('cp -p ' . self::TMP_PATH . 'webapp/database.db ' . self::DB_PATH, true);
		$this->commandManager->run('rm ' . self::TMP_PATH . 'webapp/database.db', true);
		$this->commandManager->run('cp -p ' . self::TMP_PATH . 'webapp/* ' . self::CONF_PATH, true);
		$this->commandManager->run('rm -rf ' . self::TMP_PATH . 'webapp', true);
		$this->commandManager->run('cp -p ' . self::TMP_PATH . 'nginx/* ' . self::NGINX_PATH, true);
		$this->commandManager->run('rm -rf ' . self::TMP_PATH . 'nginx', true);
	}

}
