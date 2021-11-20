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

namespace App\GatewayModule\Models;

use App\ConfigModule\Exceptions\IncompleteConfigurationException;
use App\ConfigModule\Exceptions\NotDaemonConfigurationException;
use App\ConfigModule\Models\ComponentSchemaManager;
use App\CoreModule\Exceptions\InvalidJsonException;
use App\CoreModule\Exceptions\NonexistentJsonSchemaException;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\FeatureManager;
use App\CoreModule\Models\ZipArchiveManager;
use App\GatewayModule\Exceptions\InvalidBackupContentException;
use App\GatewayModule\Models\DaemonDirectories;
use App\GatewayModule\Models\Utils\GatewayInfoUtil;
use App\ServiceModule\Exceptions\UnsupportedInitSystemException;
use App\ServiceModule\Models\ServiceManager;
use DateTime;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;
use Throwable;
use ZipArchive;

/**
 * Tool for migrating configuration
 */
class BackupManager {

	/**
	 * Path to gateway file
	 */
	private const GW_PATH = '/etc/iqrf-gateway.json';

	/**
	 * Path to hosts file
	 */
	private const HOSTS_PATH = '/etc/hosts';

	/**
	 * Path to hostname file
	 */
	private const HOSTNAME_PATH = '/etc/hostname';

	/**
	 * Path to mender configuration
	 */
	private const MENDER_DIR = '/etc/mender/';

	/**
	 * Path to pixla configuration
	 */
	private const PIXLA_DIR = '/etc/gwman/';

	/**
	 * Path to NetworkManager configuration
	 */
	private const NM_DIR = '/etc/NetworkManager/';

	/**
	 * Path to webapp configuration
	 */
	private const WEBAPP_DIR = __DIR__ . '/../../config/';

	/**
	 * @var string Path to IQRF Gateway Controller configuration directory
	 */
	private $controllerConfigDirectory;

	/**
	 * @var string Path to IQRF Gateway Translator configuration directory
	 */
	private $translatorConfigDirectory;

	/**
	 * @var string Path to IQRF Gateway Uploader configuration directory
	 */
	private $uploaderConfigDirectory;

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * @var DaemonDirectories IQRF Gateway Daemon's directory manager
	 */
	private $daemonDirectories;

	/**
	 * @var FeatureManager Feature manager
	 */
	private $featureManager;

	/**
	 * @var GatewayInfoUtil Gateway info manager
	 */
	private $gwInfo;

	/**
	 * @var ComponentSchemaManager JSON schema manager
	 */
	private $schemaManager;

	/**
	 * @var ServiceManager Service manager
	 */
	private $serviceManager;

	/**
	 * @var ZipArchiveManager ZIP archive manager
	 */
	private $zipManager;

	/**
	 * Constructor
	 * @param string $controllerConfigDirectory Path to IQRF Gateway Controller configuration directory
	 * @param string $translatorConfigDirectory Path to IQRF Gateway Translator configuration directory
	 * @param string $uploaderConfigDirectory Path to IQRF Gateway Uploader configuration directory
	 * @param DaemonDirectories $daemonDirectories IQRF Gateway Daemon's directory manager
	 * @param CommandManager $commandManager Command manager
	 * @param FeatureManager $featureManager Feature manager
	 * @param ComponentSchemaManager $schemaManager JSON schema manager
	 * @param ServiceManager $serviceManager Service manager
	 * @param GatewayInfoUtil $gwInfo Gateway information
	 */
	public function __construct(string $controllerConfigDirectory, string $translatorConfigDirectory, string $uploaderConfigDirectory, DaemonDirectories $daemonDirectories, CommandManager $commandManager, FeatureManager $featureManager, ComponentSchemaManager $schemaManager, ServiceManager $serviceManager, GatewayInfoUtil $gwInfo) {
		$this->daemonDirectories = $daemonDirectories;
		$this->controllerConfigDirectory = $controllerConfigDirectory;
		$this->translatorConfigDirectory = $translatorConfigDirectory;
		$this->uploaderConfigDirectory = $uploaderConfigDirectory;
		$this->commandManager = $commandManager;
		$this->featureManager = $featureManager;
		$this->schemaManager = $schemaManager;
		$this->serviceManager = $serviceManager;
		$this->gwInfo = $gwInfo;
	}

	/**
	 * Creates a gateway backup zip archive
	 * @param array $params Backup parameters
	 * @return string Path to backup zip archive
	 */
	public function backup(array $params): string {
		$path = $this->getArchivePath();
		$this->zipManager = new ZipArchiveManager($path);
		if ($params['software']['controller']) {
			$this->backupController();
		}
		if ($params['software']['daemon']) {
			$this->backupDaemon();
		}
		if ($params['software']['translator']) {
			$this->backupTranslator();
		}
		if ($params['software']['uploader']) {
			$this->backupUploader();
		}
		if ($params['software']['webapp']) {
			$this->backupWebapp();
		}
		if ($params['software']['mender']) {
			$this->backupMender();
		}
		if ($params['software']['pixla']) {
			$this->backupPixla();
		}
		if ($params['system']['metadata']) {
			$this->backupMetadata();
		}
		if ($params['system']['hostname']) {
			$this->backupHost();
		}
		if ($params['system']['network']) {
			$this->backupNetworkManager();
		}
		if ($params['system']['ntp']){
			$this->backupNtp();
		}
		if ($params['system']['journal']) {
			$this->backupJournal();
		}
		$this->zipManager->close();
		return $path;
	}

	/**
	 * Backup controller configuration
	 */
	private function backupController(): void {
		if (file_exists($this->controllerConfigDirectory . 'config.json')) {
			$this->zipManager->addFolder($this->controllerConfigDirectory, 'controller');
		}
	}

	/**
	 * Backup daemon configuration
	 */
	private function backupDaemon(): void {
		$this->zipManager->addFolder($this->daemonDirectories->getConfigurationDir(), 'daemon');
		$this->zipManager->addFolder($this->daemonDirectories->getCacheDir() . '/scheduler', 'daemon/scheduler');
		if ($this->zipManager->exist('daemon/scheduler/schema/')) {
			$this->zipManager->deleteDirectory('daemon/scheduler/schema');
		}
	}

	/**
	 * Backup translator configuration
	 */
	private function backupTranslator(): void {
		if (file_exists($this->translatorConfigDirectory . 'config.json')) {
			$this->zipManager->addFolder($this->translatorConfigDirectory, 'translator');
		}
	}

	/**
	 * Backup uploader configuration
	 */
	private function backupUploader(): void {
		if (file_exists($this->uploaderConfigDirectory . 'config.json')) {
			$this->zipManager->addFolder($this->uploaderConfigDirectory, 'uploader');
		}
	}

	/**
	 * Backup webapp data
	 */
	private function backupWebapp(): void {
		$this->zipManager->addFile(self::WEBAPP_DIR . 'database.db', 'webapp/database.db');
		$this->zipManager->addFile(self::WEBAPP_DIR . 'features.neon', 'webapp/features.neon');
		$this->zipManager->addFile(self::WEBAPP_DIR . 'iqrf-repository.neon', 'webapp/iqrf-repository.neon');
		$this->zipManager->addFile(self::WEBAPP_DIR . 'smtp.neon', 'webapp/smtp.neon');
	}

	/**
	 * Backup mender client and connect configuration
	 */
	private function backupMender(): void {
		$file = self::MENDER_DIR . 'mender.conf';
		if (file_exists($file)) {
			$this->zipManager->addFile($file, 'mender/mender.conf');
		}
		$file = self::MENDER_DIR . 'mender-connect.conf';
		if (file_exists($file)) {
			$this->zipManager->addFile($file, 'mender/mender-connect.conf');
		}
	}

	/**
	 * Backup pixla configuration
	 */
	private function backupPixla(): void {
		if (file_exists(self::PIXLA_DIR)) {
			$this->zipManager->addFolder(self::PIXLA_DIR, 'pixla');
		}
	}

	/**
	 * Backup gateway metadata
	 */
	private function backupMetadata(): void {
		if (file_exists(self::GW_PATH)) {
			$this->zipManager->addFile(self::GW_PATH, 'gateway/iqrf-gateway.json');
		}
	}

	/**
	 * Backup hosts and hostname
	 */
	private function backupHost(): void {
		$this->zipManager->addFile(self::HOSTNAME_PATH, 'host/hostname');
		$this->zipManager->addFile(self::HOSTS_PATH, 'host/hosts');
	}

	/**
	 * Backup NetworkManager configuration and connection profiles
	 */
	private function backupNetworkManager(): void {
		$this->zipManager->addFile(self::NM_DIR . 'NetworkManager.conf', 'nm/NetworkManager.conf');
		$this->zipManager->addFolder(self::NM_DIR . 'system-connections', 'nm/system-connections');
	}

	/**
	 * Backup NTP configuration
	 */
	private function backupNtp(): void {
		$path = $this->featureManager->get('ntp')['path'];
		if (file_exists($path)) {
			$this->zipManager->addFile($path, 'ntp/timesyncd.conf');
		}
	}

	/**
	 * Backup journal configuration
	 */
	private function backupJournal(): void {
		$path = $this->featureManager->get('systemdJournal')['path'];
		if (file_exists($path)) {
			$this->zipManager->addFile($path, 'journal/journald.conf');
		}
	}

	/**
	 * Generates backup archive path
	 * @return string Path to backup archive
	 */
	private function getArchivePath(): string {
		try {
			$date = new DateTime();
			$gwId = $this->gwInfo->getProperty('gwId');
			$gwId = $gwId === null ? '' : strtolower($gwId);
			$path = sprintf('/tmp/iqrf-gateway-backup_%s_%s.zip', $gwId, $date->format('c'));
		} catch (Throwable $e) {
			$path = '/tmp/iqrf-gateway-backup.zip';
		}
		return $path;
	}

	/**
	 * Restores gateway from backed up configuration
	 * @param string $path Path to archive containing configuration
	 */
	public function restore(string $path): void {
		$this->zipManager = new ZipArchiveManager($path, ZipArchive::CREATE);
		$this->validate();
	}

	/**
	 * Validates contents of backup archive
	 */
	private function validate(): void {
		$whitelistDirs = [
			'controller/',
			'daemon/',
			'daemon/certs/',
			'daemon/cfgSchemas/',
			'daemon/scheduler/',
			'gateway/',
			'host/',
			'journal/',
			'mender/',
			'nm/',
			'nm/system-connections/',
			'ntp/',
			'pixla/',
			'translator/',
			'uploader/',
			'webapp/',
		];
		$files = $this->zipManager->listFiles();
		foreach ($files as $file) {
			$valid = false;
			foreach ($whitelistDirs as $dir) {
				if (strpos($file, $dir) === 0) {
					$valid = true;
				}
			}
			if (!$valid) {
				throw new InvalidBackupContentException('Unexpected file found in backup archive: ' . $file);
			}
			if (strpos($file, 'controller/') === 0) {
				$this->isWhitelisted(['config.json'], $file);
			} elseif (strpos($file, 'daemon/') === 0) {
				$matches = Strings::match($file, '~^\w+\_\_\w+\.json$~');
				if (!is_array($matches)) {
					continue;
				}
				$json = Json::decode($this->zipManager->openFile($file));
				try {
					$this->schemaManager->setSchema($json->component);
				} catch (NonexistentJsonSchemaException $e) {
					continue;
				}
				try {
					$this->schemaManager->validate($json);
				} catch (InvalidJsonException $e) {
					throw new InvalidBackupContentException('Failed to validate file ' . $file . ' against JSON schema.');
				}
			} elseif (strpos($file, 'gateway/') === 0) {
				$wl = ['iqrf-gateway.json'];
				$this->isWhitelisted($wl, $file);
			} elseif (strpos($file, 'host/') === 0) {
				$wl = ['hostname', 'hosts'];
				$this->isWhitelisted($wl, $file);
			} elseif (strpos($file, 'journal/') === 0) {
				$wl = ['journald.conf'];
				$this->isWhitelisted($wl, $file);
			} elseif (strpos($file, 'mender/') === 0) {
				$wl = ['mender.conf', 'mender-connect.conf'];
				$this->isWhitelisted($wl, $file);
			} elseif (strpos($file, 'nm/system-connections/') === 0) {
				continue;
			} elseif (strpos($file, 'nm/') === 0) {
				$wl = ['NetworkManager.conf'];
				$this->isWhitelisted($wl, $file);
			} elseif (strpos($file, 'ntp/') === 0) {
				$wl = ['timesyncd.conf'];
				$this->isWhitelisted($wl, $file);
			} elseif (strpos($file, 'pixla/') == 0) {
				$wl = ['customer_id', 'gw_id'];
				$this->isWhitelisted($wl, $file);
			} elseif (strpos($file, 'translator/') === 0) {
				$wl = ['config.json'];
				$this->isWhitelisted($wl, $file);
			} elseif (strpos($file, 'uploader/') === 0) {
				$wl = ['config.json'];
				$this->isWhitelisted($wl, $file);
			} elseif (strpos($file, 'webapp/') === 0) {
				$wl = ['database.db', 'features.neon', 'iqrf-repository.neon', 'smtp.neon'];
				$this->isWhitelisted($wl, $file);
			} else {
				throw new InvalidBackupContentException('Unexpected file found in backup archive: ' . $file);
			}
		}
	}

	private function isWhitelisted(array $whitelist, string $file): void {
		if (!in_array(basename($file), $whitelist)) {
			throw new InvalidBackupContentException('Unexpected file found in backup archive: ' . $file);
		}
	}

	/**
	 * Extracts an archive with configurations
	 * @param string $path Path to archive with scheduler configuration
	 * @throws IncompleteConfigurationException
	 * @throws JsonException
	 * @throws UnsupportedInitSystemException
	 */
	public function extractArchive(string $path): void {
		$zipManager = new ZipArchiveManager($path, ZipArchive::CREATE);
		$directories = [
			$this->daemonDirectories->getConfigurationDir(),
		];
		if ($zipManager->exist('controller/config.json')) {
			$directories[] = $this->controllerConfigDirectory;
		}
		if ($zipManager->exist('translator/config.json')) {
			$directories[] = $this->translatorConfigDirectory;
		}
		if ($zipManager->exist('uploader/config.json')) {
			$directories[] = $this->uploaderConfigDirectory;
		}
		foreach ($directories as $directory) {
			$this->commandManager->run('rm -rf ' . $directory, true);
			$this->commandManager->run('mkdir ' . $directory, true);
		}
		$this->changeOwner();
		$this->extractDaemon($zipManager);
		$this->extractTranslator($zipManager);
		$this->extractUploader($zipManager);
		$zipManager->close();
		$this->serviceManager->restart();
	}

	/**
	 * Extracts IQRF Gateway Controller's configuration
	 * @param ZipArchiveManager $archiveManager ZIP archive manager
	 */
	private function restoreController(): void {
		$this->zipManager->extract($this->controllerConfigDirectory, 'controller/config.json');
		$this->commandManager->run('cp -p ' . $this->controllerConfigDirectory . 'controller/config.json ' . $this->controllerConfigDirectory . 'config.json', true);
		$this->commandManager->run('rm -rf ' . $this->controllerConfigDirectory . 'controller', true);
	}

	/**
	 * Extracts IQRF Gateway Daemon's configuration
	 * @param ZipArchiveManager $archiveManager ZIP archive manager
	 */
	private function extractDaemon(ZipArchiveManager $archiveManager): void {
		foreach ($archiveManager->listFiles() as $file) {
			var_dump($this->daemonDirectories->getCacheDir());
			if (strpos($file, 'daemon/scheduler/') === 0) {
				$archiveManager->extract($this->daemonDirectories->getCacheDir(), $file);
			}
			if (strpos($file, 'daemon/') === 0) {
				$archiveManager->extract($this->daemonDirectories->getConfigurationDir(), $file);
			}
		}
		$this->commandManager->run('cp -rfp ' . $this->daemonDirectories->getCacheDir() . 'daemon/scheduler/* ' . $this->daemonDirectories->getCacheDir() . 'scheduler', true);
		$this->commandManager->run('rm -rf ' . $this->daemonDirectories->getCacheDir() . 'daemon', true);
		$this->commandManager->run('cp -rfp ' . $this->daemonDirectories->getConfigurationDir() . 'daemon/* ' . $this->daemonDirectories->getConfigurationDir(), true);
		$this->commandManager->run('rm -rf ' . $this->daemonDirectories->getConfigurationDir() . 'daemon', true);
	}

	/**
	 * Extracts IQRF Gateway Translator's configuration
	 * @param ZipArchiveManager $archiveManager ZIP archive manager
	 */
	private function extractTranslator(ZipArchiveManager $archiveManager): void {
		if ($archiveManager->exist('translator/config.json')) {
			$archiveManager->extract($this->translatorConfigDirectory, 'translator/config.json');
			$this->commandManager->run('cp -p ' . $this->translatorConfigDirectory . 'translator/config.json ' . $this->translatorConfigDirectory . 'config.json', true);
			$this->commandManager->run('rm -rf ' . $this->translatorConfigDirectory . 'translator', true);
		}
	}

	/**
	 * Extracts IQRF Gateway Translator's configuration
	 * @param ZipArchiveManager $archiveManager ZIP archive manager
	 */
	private function extractUploader(ZipArchiveManager $archiveManager): void {
		if ($archiveManager->exist('uploader/config.json')) {
			$archiveManager->extract($this->uploaderConfigDirectory, 'uploader/config.json');
			$this->commandManager->run('cp -p ' . $this->uploaderConfigDirectory . 'uploader/config.json ' . $this->uploaderConfigDirectory . 'config.json', true);
			$this->commandManager->run('rm -rf ' . $this->uploaderConfigDirectory . 'uploader', true);
		}
	}

	/**
	 * Changes ownership of directory for JSON configuration files of IQRF Gateway Daemon
	 */
	private function changeOwner(): void {
		$dirs = [$this->daemonDirectories->getConfigurationDir()];
		if (file_exists($this->controllerConfigDirectory)) {
			$dirs[] = $this->controllerConfigDirectory;
		}
		if (file_exists($this->translatorConfigDirectory)) {
			$dirs[] = $this->translatorConfigDirectory;
		}
		if (file_exists($this->uploaderConfigDirectory)) {
			$dirs[] = $this->uploaderConfigDirectory;
		}
		$posixUser = posix_getpwuid(posix_geteuid());
		$owner = $posixUser['name'] . ':' . posix_getgrgid($posixUser['gid'])['name'];
		foreach ($dirs as $dir) {
			$this->commandManager->run('chown ' . $owner . ' ' . $dir, true);
			$this->commandManager->run('chown -R ' . $owner . ' ' . $dir, true);
		}
	}

}
