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
use App\GatewayModule\Models\Backup\JournalBackup;
use App\GatewayModule\Models\Backup\MenderBackup;
use App\GatewayModule\Models\Backup\MonitBackup;
use App\GatewayModule\Models\Backup\NetworkManagerBackup;
use App\GatewayModule\Models\Backup\NtpBackup;
use App\GatewayModule\Models\Backup\PixlaBackup;
use App\GatewayModule\Models\Backup\TranslatorBackup;
use App\GatewayModule\Models\Backup\UploaderBackup;
use App\GatewayModule\Models\Backup\WebappBackup;
use App\GatewayModule\Models\Utils\GatewayInfoUtil;
use App\ServiceModule\Models\ServiceManager;
use DateTime;
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
	 * @param array<string, array<string, bool>> $params Backup parameters
	 * @return string Path to backup zip archive
	 */
	public function backup(array $params): string {
		$path = $this->getArchivePath();
		$this->zipManager = new ZipArchiveManager($path);
		$managers = [];
		if ($params['system']['hostname']) {
			$managers[] = new HostBackup($this->commandManager, $this->zipManager);
		}
		if ($params['software']['iqrf']) {
			$managers[] = new GatewayFileBackup($this->commandManager, $this->zipManager);
			$managers[] = new ControllerBackup($this->controllerConfigDirectory, $this->commandManager, $this->zipManager);
			$managers[] = new DaemonBackup($this->daemonDirectories, $this->commandManager, $this->zipManager);
			$managers[] = new TranslatorBackup($this->translatorConfigDirectory, $this->commandManager, $this->zipManager);
			$managers[] = new UploaderBackup($this->uploaderConfigDirectory, $this->commandManager, $this->zipManager);
			$managers[] = new WebappBackup($this->commandManager, $this->zipManager);
		}
		if ($params['system']['journal']) {
			$managers[] = new JournalBackup($this->featureManager->get('systemdJournal')['path'], $this->commandManager, $this->zipManager);
		}
		if ($params['software']['mender']) {
			$managers[] = new MenderBackup($this->commandManager, $this->zipManager);
		}
		if ($params['software']['monit']) {
			$managers[] = new MonitBackup($this->commandManager, $this->zipManager);
		}
		if ($params['system']['network']) {
			$managers[] = new NetworkManagerBackup($this->commandManager, $this->zipManager);
		}
		if ($params['system']['ntp']) {
			$managers[] = new NtpBackup($this->featureManager->get('ntp')['path'], $this->commandManager, $this->zipManager);
		}
		if ($params['software']['pixla']) {
			$managers[] = new PixlaBackup($this->commandManager, $this->zipManager);
		}
		foreach ($managers as $manager) {
			$manager->backup();
		}
		if ($this->zipManager->isEmpty()) {
			throw new ZipEmptyException('Nothing to backup.');
		}
		$this->zipManager->close();
		$this->commandManager->run('rm -rf ' . self::TMP_PATH, true);
		return $path;
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
		$managers = [];
		if ($this->zipManager->exist('gateway/')) {
			$managers[] = new GatewayFileBackup($this->commandManager, $this->zipManager);
		}
		if ($this->zipManager->exist('controller/')) {
			$managers[] = new ControllerBackup($this->controllerConfigDirectory, $this->commandManager, $this->zipManager);
		}
		if ($this->zipManager->exist('daemon/')) {
			$managers[] = new DaemonBackup($this->daemonDirectories, $this->commandManager, $this->zipManager);
		}
		if ($this->zipManager->exist('host/')) {
			$managers[] = new HostBackup($this->commandManager, $this->zipManager);
		}
		if ($this->zipManager->exist('journal/')) {
			$managers[] = new JournalBackup($this->featureManager->get('systemdJournal')['path'], $this->commandManager, $this->zipManager);
		}
		if ($this->zipManager->exist('mender/')) {
			$managers[] = new MenderBackup($this->commandManager, $this->zipManager);
		}
		if ($this->zipManager->exist('nm/')) {
			$managers[] = new NetworkManagerBackup($this->commandManager, $this->zipManager);
		}
		if ($this->zipManager->exist('ntp/')) {
			$managers[] = new NtpBackup($this->featureManager->get('ntp')['path'], $this->commandManager, $this->zipManager);
		}
		if ($this->zipManager->exist('pixla/')) {
			$managers[] = new PixlaBackup($this->commandManager, $this->zipManager);
		}
		if ($this->zipManager->exist('translator/')) {
			$managers[] = new TranslatorBackup($this->translatorConfigDirectory, $this->commandManager, $this->zipManager);
		}
		if ($this->zipManager->exist('uploader/')) {
			$managers[] = new UploaderBackup($this->uploaderConfigDirectory, $this->commandManager, $this->zipManager);
		}
		if ($this->zipManager->exist('webapp/')) {
			$managers[] = new WebappBackup($this->commandManager, $this->zipManager);
		}
		foreach ($managers as $manager) {
			$manager->restore();
		}
		$this->zipManager->close();
		$this->commandManager->run('rm -rf ' . self::TMP_PATH, true);
		$this->serviceManager->restart();
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
			'translator/',
			'uploader/',
			'webapp/',
			'nginx/',
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
				$this->isWhitelisted(ControllerBackup::WHITELIST, $file);
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
					$this->zipManager->close();
					throw new InvalidBackupContentException('Failed to validate file ' . $file . ' against JSON schema.');
				}
			} elseif (strpos($file, 'gateway/') === 0) {
				$this->isWhitelisted(GatewayFileBackup::WHITELIST, $file);
			} elseif (strpos($file, 'host/') === 0) {
				$this->isWhitelisted(HostBackup::WHITELIST, $file);
			} elseif (strpos($file, 'journal/') === 0) {
				$this->isWhitelisted(JournalBackup::WHITELIST, $file);
			} elseif (strpos($file, 'mender/') === 0) {
				$this->isWhitelisted(MenderBackup::WHITELIST, $file);
			} elseif (strpos($file, 'monit/') === 0) {
				$this->isWhitelisted(MonitBackup::WHITELIST, $file);
			} elseif (strpos($file, 'nm/system-connections/') === 0) {
				continue;
			} elseif (strpos($file, 'nm/') === 0) {
				$this->isWhitelisted(NetworkManagerBackup::WHITELIST, $file);
			} elseif (strpos($file, 'ntp/') === 0) {
				$this->isWhitelisted(NtpBackup::WHITELIST, $file);
			} elseif (strpos($file, 'pixla/') === 0) {
				$this->isWhitelisted(PixlaBackup::WHITELIST, $file);
			} elseif (strpos($file, 'translator/') === 0) {
				$this->isWhitelisted(TranslatorBackup::WHITELIST, $file);
			} elseif (strpos($file, 'uploader/') === 0) {
				$this->isWhitelisted(UploaderBackup::WHITELIST, $file);
			} elseif (strpos($file, 'webapp/') === 0) {
				$this->isWhitelisted(WebappBackup::WHITELIST, $file);
			} elseif (strpos($file, 'nginx/') === 0) {
				$this->isWhitelisted(WebappBackup::NGINX_WHITELIST, $file);
			} else {
				$this->zipManager->close();
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

}
