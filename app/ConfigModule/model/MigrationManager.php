<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2018 IQRF Tech s.r.o.
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

namespace App\ConfigModule\Model;

use App\ConfigModule\Model\IncompleteConfiguration;
use App\ConfigModule\Model\InvalidConfigurationFormat;
use App\Model\CommandManager;
use App\Model\InvalidJsonException;
use App\Model\JsonSchemaManager;
use App\Model\NonExistingJsonSchemaException;
use App\Model\ZipArchiveManager;
use Nette;
use Nette\Application\Responses\FileResponse;
use Nette\Utils\ArrayHash;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Nette\Utils\Strings;

/**
 * Tool for migrationg configuration
 */
class MigrationManager {

	use Nette\SmartObject;

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * @var JsonSchemaManager JSON schema manager
	 */
	private $schemaManager;

	/**
	 * @var ZipArchiveManager ZIP archive manager
	 */
	private $zipManager;

	/**
	 * @var string Path to a directory with a configuration of IQRF Gateway Daemon
	 */
	private $configDirectory;

	/**
	 * @var string Path to ZIP archive
	 */
	private $path = '/tmp/iqrf-daemon-configuration.zip';

	/**
	 * Constructor
	 * @param string $configDirectory Path to a directory with a configuration of IQRF Gateway Daemon
	 * @param CommandManager $commandManager Command manager
	 * @param JsonSchemaManager $schemaManager JSON schema manager
	 */
	public function __construct(string $configDirectory, CommandManager $commandManager, JsonSchemaManager $schemaManager) {
		$this->configDirectory = $configDirectory;
		$this->zipManager = new ZipArchiveManager($this->path);
		$this->commandManager = $commandManager;
		$this->schemaManager = $schemaManager;
	}

	/**
	 * Download a configuration
	 * @return FileResponse HTTP response with a configuration
	 */
	public function download() {
		$now = new \DateTime();
		$fileName = 'iqrf-gateway-configuration_' . $now->format('c') . '.zip';
		$contentType = 'application/zip';
		$this->zipManager->addFolder($this->configDirectory, '');
		$this->zipManager->close();
		$response = new FileResponse($this->path, $fileName, $contentType, true);
		return $response;
	}

	/**
	 * Upload a configuration
	 * @param array $formValues Values from form
	 */
	public function upload(array $formValues) {
		$zip = $formValues['configuration'];
		if ($zip->isOk()) {
			if ($zip->getContentType() !== 'application/zip') {
				throw new InvalidConfigurationFormat();
			}
			$zip->move($this->path);
			$zipManager = new ZipArchiveManager($this->path, \ZipArchive::CREATE);
			if (!$this->validate($zipManager)) {
				$zipManager->close();
				FileSystem::delete($this->path);
				throw new IncompleteConfiguration();
			}
			$this->commandManager->send('rm -rf ' . $this->configDirectory, true);
			$this->commandManager->send('mkdir ' . $this->configDirectory, true);
			$this->changeOwner();
			$zipManager->extract($this->configDirectory);
			$zipManager->close();
			FileSystem::delete($this->path);
		}
	}

	/**
	 * Change ownership of directory for JSON configuration files of IQRF Gateway Daemon
	 */
	private function changeOwner() {
		$posixUser = posix_getpwuid(posix_geteuid());
		$owner = $posixUser['name'] . ':' . posix_getgrgid($posixUser['gid'])['name'];
		$this->commandManager->send('chown ' . $owner . ' ' . $this->configDirectory, true);
		$this->commandManager->send('chown -R ' . $owner . ' ' . $this->configDirectory, true);
	}

	/**
	 * Validate JSON configuration files for IQRF Gateway Daemon
	 * @param ZipArchiveManager $zipManager ZIP Archive manager
	 * @return bool Are JSON files valid?:
	 */
	public function validate(ZipArchiveManager $zipManager): bool {
		foreach ($zipManager->listFiles() as $file) {
			if (!Strings::match($file, '~^[a-zA-Z0-9]+\_\_[a-zA-Z0-9]+\.json$~')) {
				continue;
			}
			$json = Json::decode($zipManager->openFile($file));
			if (!array_key_exists('component', $json)) {
				continue;
			}
			try {
				$this->schemaManager->setSchemaFromComponent($json['component']);
			} catch (NonExistingJsonSchemaException $e) {
				continue;
			}
			try {
				$this->schemaManager->validate(ArrayHash::from($json));
			} catch (InvalidJsonException $e) {
				return false;
			}
		}
		return true;
	}

}
