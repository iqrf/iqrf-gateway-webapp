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
use App\GatewayModule\Models\Utils\BackupUtil;

/**
 * Uploader backup manager
 */
class UploaderBackup implements IBackupManager {

	/**
	 * List of whitelisted files
	 */
	public const WHITELIST = [
		'config.json',
	];

	/**
	 * @var string Path to uploader configuration directory
	 */
	private $path;

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * Constructor
	 * @param string $path Path to uploader configuration directory
	 * @param CommandManager $commandManager Command manager
	 */
	public function __construct(string $path, CommandManager $commandManager) {
		$this->path = $path;
		$this->commandManager = $commandManager;
	}

	/**
	 * Performs Uploader backup
	 * @param array<string, array<string, bool>> $params Request parameters
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function backup(array $params, ZipArchiveManager $zipManager): void {
		if (!$params['software']['iqrf']) {
			return;
		}
		if (file_exists($this->path)) {
			$zipManager->addFolder($this->path, 'uploader');
		}
	}

	/**
	 * Performs Uploader restore
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function restore(ZipArchiveManager $zipManager): void {
		if (!$zipManager->exist('uploader/')) {
			return;
		}
		BackupUtil::recreateDirectories([$this->path]);
		$zipManager->extract($this->path, 'uploader/config.json');
		$this->commandManager->run('cp -p ' . $this->path . 'uploader/config.json ' . $this->path . 'config.json', true);
		$this->commandManager->run('rm -rf ' . $this->path . 'uploader', true);
	}

}
