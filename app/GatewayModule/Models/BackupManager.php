<?php

/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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
use App\CoreModule\Models\ZipArchiveManager;
use App\GatewayModule\Exceptions\InvalidBackupContentException;
use App\GatewayModule\Exceptions\InvalidGatewayFileContentException;
use App\GatewayModule\Models\Backup\ControllerBackup;
use App\GatewayModule\Models\Backup\GatewayFileBackup;
use App\GatewayModule\Models\Backup\HostBackup;
use App\GatewayModule\Models\Backup\IBackupManager;
use App\GatewayModule\Models\Backup\JournalBackup;
use App\GatewayModule\Models\Backup\MenderBackup;
use App\GatewayModule\Models\Backup\MonitBackup;
use App\GatewayModule\Models\Backup\NetworkManagerBackup;
use App\GatewayModule\Models\Backup\TimeBackup;
use App\GatewayModule\Models\Backup\TimesyncdBackup;
use App\GatewayModule\Models\Backup\TranslatorBackup;
use App\GatewayModule\Models\Backup\UploaderBackup;
use App\GatewayModule\Models\Backup\WebappBackup;
use App\GatewayModule\Models\Utils\GatewayInfoUtil;
use App\ServiceModule\Exceptions\NonexistentServiceException;
use App\ServiceModule\Exceptions\UnsupportedInitSystemException;
use App\ServiceModule\Models\ServiceManager;
use DateTime;
use Nette\Utils\FileSystem;
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
	 * @var string Path to temporary backup directory
	 */
	private const TMP_PATH = '/tmp/backup/';

	/**
	 * @var array<string> Services to include in backup
	 */
	private const SERVICES = [
		'apcupsd',
		'ssh',
	];

	/**
	 * @var ZipArchiveManager ZIP archive manager
	 */
	private ZipArchiveManager $zipManager;

	/**
	 * Constructor
	 * @param Array<IBackupManager> $backupManagers Backup managers
	 * @param CommandManager $commandManager Command manager
	 * @param PowerManager $powerManager Power manager
	 * @param ComponentSchemaManager $schemaManager JSON schema manager
	 * @param ServiceManager $serviceManager Service manager
	 * @param GatewayInfoUtil $gwInfo Gateway information
	 */
	public function __construct(
		private readonly array $backupManagers,
		private readonly CommandManager $commandManager,
		private readonly PowerManager $powerManager,
		private readonly ComponentSchemaManager $schemaManager,
		private readonly ServiceManager $serviceManager,
		private readonly GatewayInfoUtil $gwInfo,
	) {
	}

	/**
	 * Creates a gateway backup zip archive
	 * @param array<string, array<string, bool>> $params Backup parameters
	 * @return string Path to backup zip archive
	 * @throws JsonException
	 * @throws UnsupportedInitSystemException
	 * @throws ZipEmptyException
	 */
	public function backup(array $params): string {
		$path = $this->getArchivePath();
		$this->zipManager = new ZipArchiveManager($path);
		$services = [];
		foreach ($this->backupManagers as $manager) {
			$manager->backup($params, $this->zipManager);
			$services = array_merge($services, $manager->getServices());
		}
		$this->backupServices($services);
		if ($this->zipManager->isEmpty()) {
			throw new ZipEmptyException('Nothing to backup.');
		}
		$this->zipManager->close();
		$this->cleanup();
		return $path;
	}

	/**
	 * Restores gateway from backed up configuration
	 * @param string $path Path to archive containing configuration
	 * @return array{timestamp: int} Reboot timestamp
	 * @throws JsonException
	 * @throws UnsupportedInitSystemException
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
			$gwId = $this->gwInfo->getId();
			$path = sprintf('/tmp/iqrf-gateway-backup_%s_%s.zip', strtolower($gwId), $date->format('c'));
		} catch (Throwable $e) {
			$path = '/tmp/iqrf-gateway-backup.zip';
		}
		return $path;
	}

	/**
	 * Checks and exports status of services
	 * @param array<int, string> $services Array of services to backup
	 * @throws UnsupportedInitSystemException
	 * @throws JsonException
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
		$this->zipManager->addFileFromText('services/enabled_services.json', Json::encode($enabledServices));
	}

	/**
	 * Restores service status
	 * @throws JsonException
	 * @throws UnsupportedInitSystemException
	 */
	private function restoreServices(): void {
		if (!$this->zipManager->exist('services/')) {
			return;
		}
		$this->zipManager->extract(self::TMP_PATH, 'services/enabled_services.json');
		$services = Json::decode(FileSystem::read(self::TMP_PATH . 'services/enabled_services.json'));
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
	 * @throws JsonException
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
			'services/',
			'time/',
			'timesyncd/',
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
				$matches = Strings::match($file, '#^\w+\_\_\w+\.json$#');
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
			} elseif (Strings::startsWith($file, 'services/')) {
				$this->isWhitelisted(['enabled_services.json'], $file);
			} elseif (Strings::startsWith($file, 'time/')) {
				$this->isWhitelisted(TimeBackup::WHITELIST, $file);
			} elseif (Strings::startsWith($file, 'timesyncd/')) {
				$this->isWhitelisted(TimesyncdBackup::WHITELIST, $file);
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
	 * @throws JsonException
	 */
	private function checkImage(): void {
		$archiveGwFile = $this->zipManager->exist('gateway/');
		$fsGwFile = file_exists('/etc/iqrf-gateway.json');
		if ($archiveGwFile !== $fsGwFile) {
			throw new InvalidBackupContentException('Incompatible backup archive and target gateway.');
		}
		$restoreGwInfo = Json::decode($this->zipManager->openFile('gateway/iqrf-gateway.json'), Json::FORCE_ARRAY);
		$pattern = '/^(?\'product\'[^-]*)-(?\'os\'[^-]*)-v(?\'major\'\d+)\.(?\'minor\'\d+)\.\d+(-(alpha|beta|rc\d+))?$/';
		$restoreMatches = Strings::match($restoreGwInfo['gwImage'], $pattern);
		if ($restoreMatches === null) {
			throw new InvalidBackupContentException('Invalid backup archive gateway image version.');
		}
		$gwImage = $this->gwInfo->getImage();
		$gwMatches = Strings::match($gwImage, $pattern);
		if ($gwMatches === null) {
			throw new InvalidGatewayFileContentException('Gateway file does not contain valid image version.');
		}
		if (($gwMatches['product'] !== $restoreMatches['product']) ||
			($gwMatches['os'] !== $restoreMatches['os']) ||
			($gwMatches['major'] !== $restoreMatches['major']) ||
			($gwMatches['minor'] !== $restoreMatches['minor'])) {
			$this->zipManager->close();
			$this->cleanup();
			throw new InvalidBackupContentException('Gateway image and backup archive version mismatch.');
		}
	}

	/**
	 * Cleans up temporary backup directory
	 */
	private function cleanup(): void {
		$this->commandManager->run('rm -rf ' . escapeshellarg(self::TMP_PATH), true);
	}

}
