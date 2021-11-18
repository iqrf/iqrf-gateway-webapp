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

namespace App\ConfigModule\Models;

use App\ConfigModule\Exceptions\IncompleteConfigurationException;
use App\ConfigModule\Exceptions\NotDaemonConfigurationException;
use App\CoreModule\Exceptions\InvalidJsonException;
use App\CoreModule\Exceptions\NonexistentJsonSchemaException;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\ZipArchiveManager;
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
class MigrationManager {

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * @var DaemonDirectories IQRF Gateway Daemon's directory manager
	 */
	private $daemonDirectories;

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
	 * Constructor
	 * @param string $controllerConfigDirectory Path to IQRF Gateway Controller configuration directory
	 * @param string $translatorConfigDirectory Path to IQRF Gateway Translator configuration directory
	 * @param string $uploaderConfigDirectory Path to IQRF Gateway Uploader configuration directory
	 * @param DaemonDirectories $daemonDirectories IQRF Gateway Daemon's directory manager
	 * @param CommandManager $commandManager Command manager
	 * @param ComponentSchemaManager $schemaManager JSON schema manager
	 * @param ServiceManager $serviceManager Service manager
	 */
	public function __construct(string $controllerConfigDirectory, string $translatorConfigDirectory, string $uploaderConfigDirectory, DaemonDirectories $daemonDirectories, CommandManager $commandManager, ComponentSchemaManager $schemaManager, ServiceManager $serviceManager, GatewayInfoUtil $gwInfo) {
		$this->daemonDirectories = $daemonDirectories;
		$this->controllerConfigDirectory = $controllerConfigDirectory;
		$this->translatorConfigDirectory = $translatorConfigDirectory;
		$this->uploaderConfigDirectory = $uploaderConfigDirectory;
		$this->commandManager = $commandManager;
		$this->schemaManager = $schemaManager;
		$this->serviceManager = $serviceManager;
		$this->gwInfo = $gwInfo;
	}

	/**
	 * Create an archive with the configuration
	 * @return string Path to archive with the configuration
	 */
	public function createArchive(): string {
		try {
			$now = new DateTime();
			$gwId = $this->gwInfo->getProperty('gwId');
			$gwId = $gwId === null ? '' : strtolower($gwId) . '_';
			$path = '/tmp/iqrf-gateway-configuration_' . $gwId . $now->format('c') . '.zip';
		} catch (Throwable $e) {
			$path = '/tmp/iqrf-gateway-configuration.zip';
		}
		$zipManager = new ZipArchiveManager($path);
		$zipManager->addFolder($this->daemonDirectories->getConfigurationDir(), 'daemon');
		$zipManager->addFolder($this->daemonDirectories->getCacheDir() . '/scheduler', 'daemon/scheduler');
		if ($zipManager->exist('daemon/scheduler/schema/')) {
			$zipManager->deleteDirectory('daemon/scheduler/schema');
		}
		if (file_exists($this->controllerConfigDirectory . 'config.json')) {
			$zipManager->addFolder($this->controllerConfigDirectory, 'controller');
		}
		if (file_exists($this->translatorConfigDirectory . 'config.json')) {
			$zipManager->addFolder($this->translatorConfigDirectory, 'translator');
		}
		if (file_exists($this->uploaderConfigDirectory . 'config.json')) {
			$zipManager->addFolder($this->uploaderConfigDirectory, 'uploader');
		}
		$zipManager->close();
		return $path;
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
		if (!$this->validate($zipManager)) {
			$zipManager->close();
			throw new IncompleteConfigurationException();
		}
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
		$this->extractController($zipManager);
		$this->extractTranslator($zipManager);
		$this->extractUploader($zipManager);
		$zipManager->close();
		$this->serviceManager->restart();
	}

	/**
	 * Extracts IQRF Gateway Controller's configuration
	 * @param ZipArchiveManager $archiveManager ZIP archive manager
	 */
	private function extractController(ZipArchiveManager $archiveManager): void {
		if ($archiveManager->exist('controller/config.json')) {
			$archiveManager->extract($this->controllerConfigDirectory, 'controller/config.json');
			$this->commandManager->run('cp -p ' . $this->controllerConfigDirectory . 'controller/config.json ' . $this->controllerConfigDirectory . 'config.json', true);
			$this->commandManager->run('rm -rf ' . $this->controllerConfigDirectory . 'controller', true);
		}
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
	 * Validates JSON configuration files for IQRF Gateway Daemon
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 * @return bool Are JSON files valid?
	 * @throws JsonException
	 * @throws NotDaemonConfigurationException
	 */
	public function validate(ZipArchiveManager $zipManager): bool {
		$whitelistDirs = ['daemon/', 'controller/', 'translator/', 'uploader/', 'daemon/certs/', 'daemon/cfgSchemas/', 'daemon/scheduler/'];
		$fileList = $zipManager->listFiles();
		foreach ($fileList as $file) {
			$valid = false;
			foreach ($whitelistDirs as $dir) {
				if (strpos($file, $dir) === 0) {
					$valid = true;
				}
			}
			if (!$valid) {
				throw new NotDaemonConfigurationException();
			}
			$matches = Strings::match($file, '~^\w+\_\_\w+\.json$~');
			if (!is_array($matches)) {
				continue;
			}
			$json = Json::decode($zipManager->openFile($file));
			try {
				$this->schemaManager->setSchema($json->component);
			} catch (NonexistentJsonSchemaException $e) {
				continue;
			}
			try {
				$this->schemaManager->validate($json);
			} catch (InvalidJsonException $e) {
				return false;
			}
		}
		return true;
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
