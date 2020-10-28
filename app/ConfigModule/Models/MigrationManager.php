<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2019 IQRF Tech s.r.o.
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
	 * @var ComponentSchemaManager JSON schema manager
	 */
	private $schemaManager;

	/**
	 * @var string Path to a directory with a configuration of IQRF Gateway Daemon
	 */
	private $configDirectory;

	/**
	 * @var ServiceManager Service manager
	 */
	private $serviceManager;

	/**
	 * @var string Path to a directory with a configuration of IQRF Gateway Controller
	 */
	private $controllerConfigDirectory;

	/**
	 * @var string Path to a directory with a configuration of IQRF Gateway Translator
	 */
	private $translatorConfigDirectory;

	/**
	 * Constructor
	 * @param string $configDirectory Path to a directory with a configuration of IQRF Gateway Daemon
	 * @param CommandManager $commandManager Command manager
	 * @param ComponentSchemaManager $schemaManager JSON schema manager
	 * @param ServiceManager $serviceManager Service manager
	 */
	public function __construct(string $configDirectory, string $controllerConfigDirectory, string $translatorConfigDirectory, CommandManager $commandManager, ComponentSchemaManager $schemaManager, ServiceManager $serviceManager) {
		$this->configDirectory = $configDirectory;
		$this->controllerConfigDirectory = $controllerConfigDirectory;
		$this->translatorConfigDirectory = $translatorConfigDirectory;
		$this->commandManager = $commandManager;
		$this->schemaManager = $schemaManager;
		$this->serviceManager = $serviceManager;
	}

	/**
	 * Create an archive with the configuration
	 * @return string Path to archive with the configuration
	 */
	public function createArchive(): string {
		try {
			$now = new DateTime();
			$path = '/tmp/iqrf-gateway-configuration_' . $now->format('c') . '.zip';
		} catch (Throwable $e) {
			$path = '/tmp/iqrf-gateway-configuration.zip';
		}
		$zipManager = new ZipArchiveManager($path);
		$zipManager->addFolder($this->configDirectory, 'daemon');
		if (file_exists($this->controllerConfigDirectory . 'config.json')) {
			$zipManager->addFolder($this->controllerConfigDirectory, 'controller');
		}
		if (file_exists($this->translatorConfigDirectory . 'config.json')) {
			$zipManager->addFolder($this->translatorConfigDirectory, 'translator');
		}
		$zipManager->close();
		return $path;
	}

	/**
	 * Extracts an archive with scheduler configuration
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
		$this->commandManager->run('rm -rf ' . $this->configDirectory, true);
		$this->commandManager->run('mkdir ' . $this->configDirectory, true);
		if ($zipManager->exist('controller/config.json')) {
			if (file_exists($this->controllerConfigDirectory)) {
				$this->commandManager->run('rm -rf ' . $this->controllerConfigDirectory, true);
			}
			$this->commandManager->run('mkdir ' . $this->controllerConfigDirectory, true);
		}
		if ($zipManager->exist('translator/config.json')) {
			if (file_exists($this->translatorConfigDirectory)) {
				$this->commandManager->run('rm -rf ' . $this->translatorConfigDirectory, true);
			}
			$this->commandManager->run('mkdir ' . $this->translatorConfigDirectory, true);
		}
		$this->changeOwner();
		$fileList = $zipManager->listFiles();
		foreach ($fileList as $listItem) {
			if (strpos($listItem, 'daemon/') === 0) {
				$zipManager->extract($this->configDirectory, $listItem);
			}
		}
		$this->commandManager->run('cp -rfp ' . $this->configDirectory . 'daemon/* ' . $this->configDirectory, true);
		$this->commandManager->run('rm -rf ' . $this->configDirectory . 'daemon', true);
		if ($zipManager->exist('controller/config.json')) {
			$zipManager->extract($this->controllerConfigDirectory, 'controller/config.json');
			$this->commandManager->run('cp -p ' . $this->controllerConfigDirectory . 'controller/config.json ' . $this->controllerConfigDirectory . 'config.json', true);
			$this->commandManager->run('rm -rf ' . $this->controllerConfigDirectory . 'controller', true);
		}
		if ($zipManager->exist('translator/config.json')) {
			$zipManager->extract($this->translatorConfigDirectory, 'controller/config.json');
			$this->commandManager->run('cp -p ' . $this->translatorConfigDirectory . 'translator/config.json ' . $this->translatorConfigDirectory . 'config/json', true);
			$this->commandManager->run('rm -rf ' . $this->translatorConfigDirectory . 'translator', true);
		}
		$zipManager->close();
		$this->serviceManager->restart();
	}

	/**
	 * Validates JSON configuration files for IQRF Gateway Daemon
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 * @return bool Are JSON files valid?
	 * @throws JsonException
	 */
	public function validate(ZipArchiveManager $zipManager): bool {
		$whitelistDirs = ['daemon/', 'controller/', 'translator/', 'daemon/certs/', 'daemon/cfgSchemas/', 'daemon/scheduler/'];
		$fileList = $zipManager->listFiles();
		$myFile = fopen('/home/test.txt', 'w');
		foreach ($fileList as $file) {
			fwrite($myFile, $file);
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
		fclose($myFile);
		return true;
	}

	/**
	 * Changes ownership of directory for JSON configuration files of IQRF Gateway Daemon
	 */
	private function changeOwner(): void {
		$posixUser = posix_getpwuid(posix_geteuid());
		$owner = $posixUser['name'] . ':' . posix_getgrgid($posixUser['gid'])['name'];
		$this->commandManager->run('chown ' . $owner . ' ' . $this->configDirectory, true);
		$this->commandManager->run('chown -R ' . $owner . ' ' . $this->configDirectory, true);
		if (file_exists($this->controllerConfigDirectory)) {
			$this->commandManager->run('chown ' . $owner . ' ' . $this->controllerConfigDirectory, true);
			$this->commandManager->run('chown -R ' . $owner . ' ' . $this->controllerConfigDirectory, true);
		}
		if (file_exists($this->translatorConfigDirectory)) {
			$this->commandManager->run('chown ' . $owner . ' ' . $this->translatorConfigDirectory, true);
			$this->commandManager->run('chown -R ' . $owner . ' ' . $this->translatorConfigDirectory, true);
		}
	}

}
