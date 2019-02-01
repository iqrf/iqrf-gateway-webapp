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
use App\ConfigModule\Exceptions\InvalidConfigurationFormatException;
use App\CoreModule\Exceptions\InvalidJsonException;
use App\CoreModule\Exceptions\NonExistingJsonSchemaException;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\JsonSchemaManager;
use App\CoreModule\Models\ZipArchiveManager;
use DateTime;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\FileResponse;
use Nette\SmartObject;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;
use ZipArchive;

/**
 * Tool for migrating configuration
 */
class MigrationManager {

	use SmartObject;

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * @var JsonSchemaManager JSON schema manager
	 */
	private $schemaManager;

	/**
	 * @var ZipArchiveManager ZIP archive manager for a downloaded configuration
	 */
	private $zipManagerDownload;

	/**
	 * @var ZipArchiveManager ZIP archive manager for a uploaded configuration
	 */
	private $zipManagerUpload;

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
		$this->commandManager = $commandManager;
		$this->schemaManager = $schemaManager;
	}

	/**
	 * Download a configuration
	 * @return FileResponse HTTP response with a configuration
	 * @throws BadRequestException
	 */
	public function download(): FileResponse {
		$this->zipManagerDownload = new ZipArchiveManager($this->path);
		$now = new DateTime();
		$fileName = 'iqrf-gateway-configuration_' . $now->format('c') . '.zip';
		$contentType = 'application/zip';
		$this->zipManagerDownload->addFolder($this->configDirectory, '');
		$this->zipManagerDownload->close();
		$response = new FileResponse($this->path, $fileName, $contentType, true);
		return $response;
	}

	/**
	 * Upload a configuration
	 * @param mixed[] $formValues Values from form
	 * @throws IncompleteConfigurationException
	 * @throws InvalidConfigurationFormatException
	 * @throws JsonException
	 */
	public function upload(array $formValues): void {
		$zip = $formValues['configuration'];
		if (!$zip->isOk()) {
			throw new InvalidConfigurationFormatException();
		}
		if ($zip->getContentType() !== 'application/zip') {
			throw new InvalidConfigurationFormatException();
		}
		$zip->move($this->path);
		$this->zipManagerUpload = new ZipArchiveManager($this->path, ZipArchive::CREATE);
		if (!$this->validate($this->zipManagerUpload)) {
			$this->zipManagerUpload->close();
			FileSystem::delete($this->path);
			throw new IncompleteConfigurationException();
		}
		$this->commandManager->send('rm -rf ' . $this->configDirectory, true);
		$this->commandManager->send('mkdir ' . $this->configDirectory, true);
		$this->changeOwner();
		$this->zipManagerUpload->extract($this->configDirectory);
		$this->zipManagerUpload->close();
		FileSystem::delete($this->path);
	}

	/**
	 * Validate JSON configuration files for IQRF Gateway Daemon
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 * @return bool Are JSON files valid?
	 * @throws JsonException
	 */
	public function validate(ZipArchiveManager $zipManager): bool {
		foreach ($zipManager->listFiles() as $file) {
			$matches = Strings::match($file, '~^[a-zA-Z0-9]+\_\_[a-zA-Z0-9]+\.json$~');
			if (!is_array($matches)) {
				continue;
			}
			$jsonFile = $zipManager->openFile($file);
			$json = Json::decode($jsonFile, Json::FORCE_ARRAY);
			try {
				$this->schemaManager->setSchemaFromComponent($json['component']);
			} catch (NonExistingJsonSchemaException $e) {
				continue;
			}
			try {
				$this->schemaManager->validate(Json::decode($jsonFile));
			} catch (InvalidJsonException $e) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Change ownership of directory for JSON configuration files of IQRF Gateway Daemon
	 */
	private function changeOwner(): void {
		$posixUser = posix_getpwuid(posix_geteuid());
		$owner = $posixUser['name'] . ':' . posix_getgrgid($posixUser['gid'])['name'];
		$this->commandManager->send('chown ' . $owner . ' ' . $this->configDirectory, true);
		$this->commandManager->send('chown -R ' . $owner . ' ' . $this->configDirectory, true);
	}

}
