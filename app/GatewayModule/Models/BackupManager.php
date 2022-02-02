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

use App\ConfigModule\Models\ComponentSchemaManager;
use App\CoreModule\Exceptions\InvalidJsonException;
use App\CoreModule\Exceptions\NonexistentJsonSchemaException;
use App\CoreModule\Exceptions\ZipEmptyException;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\FeatureManager;
use App\CoreModule\Models\ZipArchiveManager;
use App\GatewayModule\Exceptions\InvalidBackupContentException;
use App\GatewayModule\Models\Backup\ControllerBackup;
use App\GatewayModule\Models\Backup\DaemonBackup;
use App\GatewayModule\Models\Backup\GatewayFileBackup;
use App\GatewayModule\Models\Backup\HostBackup;
use App\GatewayModule\Models\Backup\IBackupManager;
use App\GatewayModule\Models\Backup\JournalBackup;
use App\GatewayModule\Models\Backup\MenderBackup;
use App\GatewayModule\Models\Backup\MonitBackup;
use App\GatewayModule\Models\Backup\NetworkManagerBackup;
use App\GatewayModule\Models\Backup\NtpBackup;
use App\GatewayModule\Models\Backup\TimeBackup;
use App\GatewayModule\Models\Backup\TranslatorBackup;
use App\GatewayModule\Models\Backup\UploaderBackup;
use App\GatewayModule\Models\Backup\WebappBackup;
use App\GatewayModule\Models\Utils\GatewayInfoUtil;
use App\ServiceModule\Exceptions\NonexistentServiceException;
use App\ServiceModule\Models\ServiceManager;
use DateTime;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Throwable;
use ZipArchive;

/**
 * Tool for migrating configuration
 */
class BackupManager {

	/**
	 * Path to temporary backup directory
	 */
	private const TMP_PATH = '/tmp/backup/';

	/**
	 * Services to include in backup
	 */
	private const SERVICES = [
		'gwman-client',
		'ssh',
	];


	/**
	 * @var IBackupManager[] Backup managers
	 */
	private $backupManagers;

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
	 * @var PowerManager Power manager
	 */
	private $powerManager;

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
	 * @param IBackupManager[] $backupManagers Backup managers
	 * @param DaemonDirectories $daemonDirectories IQRF Gateway Daemon's directory manager
	 * @param CommandManager $commandManager Command manager
	 * @param FeatureManager $featureManager Feature manager
	 * @param PowerManager $powerManager Power manager
	 * @param ComponentSchemaManager $schemaManager JSON schema manager
	 * @param ServiceManager $serviceManager Service manager
	 * @param GatewayInfoUtil $gwInfo Gateway information
	 */
	public function __construct(string $controllerConfigDirectory, string $translatorConfigDirectory, string $uploaderConfigDirectory, array $backupManagers, DaemonDirectories $daemonDirectories, CommandManager $commandManager, FeatureManager $featureManager, PowerManager $powerManager, ComponentSchemaManager $schemaManager, ServiceManager $serviceManager, GatewayInfoUtil $gwInfo) {
		$this->daemonDirectories = $daemonDirectories;
		$this->controllerConfigDirectory = $controllerConfigDirectory;
		$this->translatorConfigDirectory = $translatorConfigDirectory;
		$this->uploaderConfigDirectory = $uploaderConfigDirectory;
		$this->backupManagers = $backupManagers;
		$this->commandManager = $commandManager;
		$this->featureManager = $featureManager;
		$this->powerManager = $powerManager;
		$this->schemaManager = $schemaManager;
		$this->serviceManager = $serviceManager;
		$this->gwInfo = $gwInfo;
	}

	/**
	 * Creates a gateway backup zip archive
	 * @param array<string, array<string, bool>> $params Backup parameters
	 * @return string Path to backup zip archive
	 */
	public function backup(array $params): string {
		$path = $this->getArchivePath();
		$this->zipManager = new ZipArchiveManager($path);
		$services = [];
		foreach ($this->backupManagers as $manager) {
			$manager->backup($params, $this->zipManager);
		}
		$this->backupServices($services);
		if ($this->zipManager->isEmpty()) {
			throw new ZipEmptyException('Nothing to backup.');
		}
		$this->zipManager->close();
		$this->commandManager->run('rm -rf ' . self::TMP_PATH, true);
		return $path;
	}

	/**
	 * Restores gateway from backed up configuration
	 * @param string $path Path to archive containing configuration
	 * @return array<string, int> Reboot timestamp
	 */
	public function restore(string $path): array {
		$this->zipManager = new ZipArchiveManager($path, ZipArchive::CREATE);
		$this->validate();
		$this->checkImage();
		foreach ($this->backupManagers as $manager) {
			$manager->restore($this->zipManager);
		}
		$this->restoreServices();
		$this->zipManager->close();
		$this->cleanup();
		return $this->powerManager->reboot();
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
	 * Checks and exports status of services
	 * @param array<int, string> $services Array of services to backup
	 */
	private function backupServices(array $services): void {
		$services = array_merge($services, self::SERVICES);
		$enabledServices = [];
		foreach ($services as $service) {
			try {
				$enabledServices[$service] = $this->serviceManager->isEnabled($service);
			} catch (NonexistentServiceException $e) {
				continue;
			}
		}
		$this->zipManager->addEmptyFolder('services');
		$this->zipManager->addFileFromText('services/enabled_services', Json::encode($enabledServices));
	}

	/**
	 * Restores service status
	 */
	private function restoreServices(): void {
		if (!$this->zipManager->exist('services/')) {
			return;
		}
		$this->zipManager->extract(self::TMP_PATH, 'services/enabled_services');
		$services = Json::decode(FileSystem::read(self::TMP_PATH . 'services/enabled_services'));
		foreach ($services as $service => $enabled) {
			try {
				if ($enabled === true) {
					$this->serviceManager->enable($service);
				} else {
					$this->serviceManager->disable($service);
				}
			} catch (NonexistentServiceException $e) {
				continue;
			}
		}
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
			'monit/',
			'nm/',
			'nm/system-connections/',
			'ntp/',
			'pixla/',
			'services/',
			'time/',
			'translator/',
			'uploader/',
			'webapp/',
			'nginx/',
		];
		$files = $this->zipManager->listFiles();
		foreach ($files as $file) {
			$valid = false;
			foreach ($whitelistDirs as $dir) {
				if (Strings::startsWith($file, $dir)) {
					$valid = true;
				}
			}
			if (!$valid) {
				throw new InvalidBackupContentException('Unexpected file found in backup archive: ' . $file);
			}
			if (Strings::startsWith($file, 'controller/')) {
				$this->isWhitelisted(ControllerBackup::WHITELIST, $file);
			} elseif (Strings::startsWith($file, 'daemon/')) {
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
					$this->zipManager->close();
					$this->cleanup();
					throw new InvalidBackupContentException('Failed to validate file ' . $file . ' against JSON schema.');
				}
			} elseif (Strings::startsWith($file, 'gateway/')) {
				$this->isWhitelisted(GatewayFileBackup::WHITELIST, $file);
			} elseif (Strings::startsWith($file, 'host/')) {
				$this->isWhitelisted(HostBackup::WHITELIST, $file);
			} elseif (Strings::startsWith($file, 'journal/')) {
				$this->isWhitelisted(JournalBackup::WHITELIST, $file);
			} elseif (Strings::startsWith($file, 'mender/')) {
				$this->isWhitelisted(MenderBackup::WHITELIST, $file);
			} elseif (Strings::startsWith($file, 'monit/')) {
				$this->isWhitelisted(MonitBackup::WHITELIST, $file);
			} elseif (Strings::startsWith($file, 'nm/system-connections/')) {
				continue;
			} elseif (Strings::startsWith($file, 'nm/')) {
				$this->isWhitelisted(NetworkManagerBackup::WHITELIST, $file);
			} elseif (Strings::startsWith($file, 'ntp/')) {
				$this->isWhitelisted(NtpBackup::WHITELIST, $file);
			} elseif (Strings::startsWith($file, 'services/')) {
				$this->isWhitelisted(['enabled_services'], $file);
			} elseif (Strings::startsWith($file, 'time/')) {
				$this->isWhitelisted(TimeBackup::WHITELIST, $file);
			} elseif (Strings::startsWith($file, 'translator/')) {
				$this->isWhitelisted(TranslatorBackup::WHITELIST, $file);
			} elseif (Strings::startsWith($file, 'uploader/')) {
				$this->isWhitelisted(UploaderBackup::WHITELIST, $file);
			} elseif (Strings::startsWith($file, 'webapp/')) {
				$this->isWhitelisted(WebappBackup::WHITELIST, $file);
			} elseif (Strings::startsWith($file, 'nginx/')) {
				$this->isWhitelisted(WebappBackup::NGINX_WHITELIST, $file);
			} else {
				$this->zipManager->close();
				$this->cleanup();
				throw new InvalidBackupContentException('Unexpected file found in backup archive: ' . $file);
			}
		}
	}

	/**
	 * Checks if file name is whitelisted
	 * @param array<string> $whitelist Whitelist of files
	 * @param string $file File name
	 */
	private function isWhitelisted(array $whitelist, string $file): void {
		if (!in_array(basename($file), $whitelist, true)) {
			$this->zipManager->close();
			throw new InvalidBackupContentException('Unexpected file found in backup archive: ' . $file);
		}
	}

	/**
	 * Checks if the backup archive is intended for the current gateway image version
	 */
	private function checkImage(): void {
		if (!$this->zipManager->exist('gateway/')) {
			return;
		}
		$this->zipManager->extract(self::TMP_PATH, 'gateway/iqrf-gateway.json');
		$restoreGwInfo = Json::decode(FileSystem::read(self::TMP_PATH . 'gateway/iqrf-gateway.json'), Json::FORCE_ARRAY);
		if ($this->gwInfo->getProperty('gwImage') !== $restoreGwInfo['gwImage']) {
			$this->zipManager->close();
			$this->cleanup();
			throw new InvalidBackupContentException('Gateway image and backup archive version mismatch.');
		}
	}

	/**
	 * Cleans up temporary backup directory
	 */
	private function cleanup(): void {
		$this->commandManager->run('rm -rf ' . self::TMP_PATH, true);
	}

}
