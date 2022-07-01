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
use App\CoreModule\Models\FeatureManager;
use App\CoreModule\Models\ZipArchiveManager;
use Nette\Utils\FileSystem;

/**
 * IQRF Software backup manager
 */
abstract class IqrfSoftwareBackup implements IBackupManager {

	/**
	 * @var string Software name
	 */
	private string $software;

	/**
	 * @var string ZIP archive directory
	 */
	private string $dir;

	/**
	 * @var string Path to configuration directory;
	 */
	private string $path;

	/**
	 * @var bool Indicates whether feature is enabled
	 */
	private bool $featureEnabled;

	/**
	 * @var CommandManager Command manager
	 */
	private CommandManager $commandManager;

	/**
	 * @var RestoreLogger Restore logger
	 */
	private RestoreLogger $restoreLogger;

	/**
	 * Constructor
	 * @param string $path Path to controller configuration directory
	 * @param CommandManager $commandManager Command manager
	 * @param FeatureManager $featureManager Feature manager
	 * @param RestoreLogger $restoreLogger Restore logger
	 */
	public function __construct(string $software, string $dir, string $feature, string $path, CommandManager $commandManager, FeatureManager $featureManager, RestoreLogger $restoreLogger) {
		$this->software = $software;
		$this->dir = $dir;
		$this->path = $path;
		$this->featureEnabled = $featureManager->get($feature)['enabled'];
		$this->commandManager = $commandManager;
		$this->restoreLogger = $restoreLogger;
	}

	/**
	 * Performs IQRF software backup
	 * @param array<string, array<string, bool>> $params Request parameters
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function backup(array $params, ZipArchiveManager $zipManager): void {
		if (!$params['software']['iqrf'] || !$this->featureEnabled) {
			return;
		}
		if (file_exists($this->path)) {
			$zipManager->addFolder($this->path, $this->dir);
		}
	}

	/**
	 * Performs IQRF software restore
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function restore(ZipArchiveManager $zipManager): void {
		if (!$zipManager->exist($this->dir . '/') || !$this->featureEnabled) {
			return;
		}
		$this->restoreLogger->log('Restoring ' . $this->software . ' configuration.');
		$this->recreateDirectories([$this->path]);
		$zipManager->extract($this->path, $this->dir . '/config.json');
		$this->commandManager->run('cp -p ' . $this->path . $this->dir . '/config.json ' . $this->path . 'config.json', true);
		FileSystem::delete($this->path . $this->dir);
	}

	/**
	 * Returns user and group string of current process
	 * @param array<int, string> $dirs Array of directory paths
	 */
	private function recreateDirectories(array $dirs): void {
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
	abstract public function getServices(): array;

}
