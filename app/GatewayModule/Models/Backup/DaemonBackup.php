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
use Nette\Utils\Strings;

/**
 * Daemon backup manager
 */
class DaemonBackup implements IBackupManager {

	/**
	 * @var array<string> Service name
	 */
	public const SERVICES = [
		'iqrf-gateway-daemon',
	];

	/**
	 * @var CommandManager Command manager
	 */
	private CommandManager $commandManager;

	/**
	 * @var DaemonDirectories IQRF Gateway Daemon's directory manager
	 */
	private DaemonDirectories $daemonDirectories;

	/**
	 * @var PrivilegedFileManager Privileged file manager
	 */
	private PrivilegedFileManager $fileManager;

	/**
	 * @var RestoreLogger Restore logger
	 */
	private RestoreLogger $restoreLogger;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 * @param DaemonDirectories $daemonDirectories Daemon directories
	 * @param RestoreLogger $restoreLogger Restore logger
	 */
	public function __construct(CommandManager $commandManager, DaemonDirectories $daemonDirectories, RestoreLogger $restoreLogger) {
		$this->commandManager = $commandManager;
		$this->daemonDirectories = $daemonDirectories;
		$this->fileManager = new PrivilegedFileManager($daemonDirectories->getConfigurationDir(), $this->commandManager);
		$this->restoreLogger = $restoreLogger;
	}

	/**
	 * Performs Daemon backup
	 * @param array<string, array<string, bool>> $params Request parameters
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function backup(array $params, ZipArchiveManager $zipManager): void {
		if (!$params['software']['iqrf']) {
			return;
		}
		$dir = $this->daemonDirectories->getConfigurationDir();
		if (!file_exists($dir)) {
			return;
		}
		foreach ($this->fileManager->listFiles() as $file) {
			$zipManager->addFileFromText('daemon/' . $file, $this->fileManager->read($file));
		}
		$zipManager->addFolder($this->daemonDirectories->getCacheDir() . 'scheduler', 'daemon/scheduler');
		if ($zipManager->exist('daemon/scheduler/schema/')) {
			$zipManager->deleteDirectory('daemon/scheduler/schema');
		}
		$zipManager->addFolder($this->daemonDirectories->getDataDir() . 'DB', 'daemon/DB');
	}

	/**
	 * Performs Daemon restore
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function restore(ZipArchiveManager $zipManager): void {
		if (!$zipManager->exist('daemon/')) {
			return;
		}
		$this->restoreLogger->log('Restoring IQRF Gateway Daemon configuration, scheduler and database.');
		$this->recreateDirectories([
			$this->daemonDirectories->getConfigurationDir(),
			$this->daemonDirectories->getDataDir() . 'DB/',
		]);
		$user = posix_getpwuid(posix_geteuid());
		$owner = $user['name'] . ':' . posix_getgrgid($user['gid'])['name'];
		$this->commandManager->run('chown -R ' . $owner . ' ' . $this->daemonDirectories->getCacheDir(), true);
		foreach ($zipManager->listFiles() as $file) {
			if (Strings::startsWith($file, 'daemon/scheduler/')) {
				$zipManager->extract($this->daemonDirectories->getCacheDir(), $file);
			} elseif (Strings::startsWith($file, 'daemon/DB/')) {
				$zipManager->extract($this->daemonDirectories->getDataDir() . 'DB/', $file);
			} elseif (Strings::startsWith($file, 'daemon/')) {
				$zipManager->extract($this->daemonDirectories->getConfigurationDir(), $file);
			}
		}
		$this->commandManager->run('cp -rfp ' . $this->daemonDirectories->getDataDir() . 'DB/daemon/DB/* ' . $this->daemonDirectories->getDataDir() . 'DB', true);
		$this->commandManager->run('rm -rf ' . $this->daemonDirectories->getDataDir() . 'DB/daemon', true);
		$this->commandManager->run('cp -rfp ' . $this->daemonDirectories->getCacheDir() . 'daemon/scheduler/* ' . $this->daemonDirectories->getCacheDir() . 'scheduler', true);
		$this->commandManager->run('rm -rf ' . $this->daemonDirectories->getCacheDir() . 'daemon', true);
		$this->commandManager->run('cp -rfp ' . $this->daemonDirectories->getConfigurationDir() . 'daemon/* ' . $this->daemonDirectories->getConfigurationDir(), true);
		$this->commandManager->run('rm -rf ' . $this->daemonDirectories->getConfigurationDir() . 'daemon', true);
		$this->fixPrivileges();
	}

	/**
	 * Fixes privileges for restored files
	 */
	private function fixPrivileges(): void {
		$this->commandManager->run('chmod -R 0666 ' . $this->daemonDirectories->getConfigurationDir(), true);
		foreach ($this->fileManager->listDirectories() as $dir) {
			$this->commandManager->run('chmod 0777 ' . $dir, true);
		}
		$this->commandManager->run('chmod 0600 ' . $this->daemonDirectories->getConfigurationDir() . 'certs/core/*', true);
	}


	/**
	 * Returns user and group string of current process
	 * @param array<int, string> $dirs Array of directory paths
	 */
	public function recreateDirectories(array $dirs): void {
		$user = posix_getpwuid(posix_geteuid());
		$owner = $user['name'] . ':' . posix_getgrgid($user['gid'])['name'];
		foreach ($dirs as $dir) {
			$this->commandManager->run('rm -rf ' . $dir, true);
			$this->commandManager->run('mkdir ' . $dir, true);
			$this->commandManager->run('chown ' . $owner . ' ' . $dir, true);
			$this->commandManager->run('chown -R ' . $owner . ' ' . $dir, true);
		}
	}
	/**
	 * Returns service names
	 * @return array<string> Service names
	 */
	public function getServices(): array {
		return self::SERVICES;
	}

}
