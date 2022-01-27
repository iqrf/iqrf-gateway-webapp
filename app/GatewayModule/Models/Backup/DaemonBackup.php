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
use App\GatewayModule\Models\DaemonDirectories;
use App\GatewayModule\Models\Utils\BackupUtil;

/**
 * Daemon backup manager
 */
class DaemonBackup implements IBackupManager {

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * @var DaemonDirectories IQRF Gateway Daemon's directory manager
	 */
	private $daemonDirectories;

	/**
	 * @var ZipArchiveManager ZIP archive manager
	 */
	private $zipManager;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function __construct(DaemonDirectories $daemonDirectories, CommandManager $commandManager, ZipArchiveManager $zipManager) {
		$this->commandManager = $commandManager;
		$this->daemonDirectories = $daemonDirectories;
		$this->zipManager = $zipManager;
	}

	/**
	 * Performs Daemon backup
	 * @param array<string, array<string, bool>> $params Request parameters
	 */
	public function backup(array $params): void {
		if (!$params['software']['iqrf']) {
			return;
		}
		$dir = $this->daemonDirectories->getConfigurationDir();
		$manager = new PrivilegedFileManager($dir, $this->commandManager);
		foreach ($manager->listFiles() as $file) {
			if (is_dir($file)) {
				continue;
			}
			$name = substr($file, strlen($dir));
			if (strlen($name) > 0) {
				$this->zipManager->addFileFromText('daemon/' . $name, $manager->read($name));
			}
		}
		$this->zipManager->addFolder($this->daemonDirectories->getCacheDir() . '/scheduler', 'daemon/scheduler');
		if ($this->zipManager->exist('daemon/scheduler/schema/')) {
			$this->zipManager->deleteDirectory('daemon/scheduler/schema');
		}
		$this->zipManager->addFolder($this->daemonDirectories->getDataDir() . '/DB', 'daemon/DB');
	}

	/**
	 * Performs Daemon restore
	 */
	public function restore(): void {
		if (!$this->zipManager->exist('daemon/')) {
			return;
		}
		BackupUtil::recreateDirectories([$this->daemonDirectories->getConfigurationDir()]);
		foreach ($this->zipManager->listFiles() as $file) {
			if (strpos($file, 'daemon/scheduler/') === 0) {
				$this->zipManager->extract($this->daemonDirectories->getCacheDir(), $file);
			} elseif (strpos($file, 'daemon/DB/') === 0) {
				$this->zipManager->extract($this->daemonDirectories->getDataDir() . '/DB/', $file);
			} elseif (strpos($file, 'daemon/') === 0) {
				$this->zipManager->extract($this->daemonDirectories->getConfigurationDir(), $file);
			}
		}
		$this->commandManager->run('cp -rfp ' . $this->daemonDirectories->getDataDir() . 'DB/daemon/DB/* ' . $this->daemonDirectories->getDataDir() . 'DB', true);
		$this->commandManager->run('rm -rf ' . $this->daemonDirectories->getDataDir() . 'DB/daemon', true);
		$this->commandManager->run('cp -rfp ' . $this->daemonDirectories->getCacheDir() . 'daemon/scheduler/* ' . $this->daemonDirectories->getCacheDir() . 'scheduler', true);
		$this->commandManager->run('rm -rf ' . $this->daemonDirectories->getCacheDir() . 'daemon', true);
		$this->commandManager->run('cp -rfp ' . $this->daemonDirectories->getConfigurationDir() . 'daemon/* ' . $this->daemonDirectories->getConfigurationDir(), true);
		$this->commandManager->run('rm -rf ' . $this->daemonDirectories->getConfigurationDir() . 'daemon', true);
		$this->fixMetadata();
	}

	/**
	 * Fixes metadata for restored files
	 */
	private function fixMetadata(): void {
		$this->commandManager->run('chmod 0600 ' . $this->daemonDirectories->getConfigurationDir() . 'certs/core/*', true);
	}

}
