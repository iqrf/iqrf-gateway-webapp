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

use App\ConfigModule\Exceptions\InvalidConfigurationFormatException;
use App\ConfigModule\Exceptions\InvalidTaskMessageException;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\ZipArchiveManager;
use DateTime;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\FileResponse;
use Nette\Http\FileUpload;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;
use Throwable;
use ZipArchive;

/**
 * Scheduler configuration migration manager
 */
class SchedulerMigrationManager {

	/**
	 * @var string Path to a directory with scheduler's configuration
	 */
	private $configDirectory;

	/**
	 * @var SchedulerSchemaManager Scheduler JSON schema manager
	 */
	private $schemaManager;

	/**
	 * Constructor
	 * @param MainManager $mainManager Main configuration manager
	 * @param SchedulerSchemaManager $schemaManager Scheduler JSON schema manager
	 * @param CommandManager $commandManager Command manager
	 */
	public function __construct(MainManager $mainManager, SchedulerSchemaManager $schemaManager, CommandManager $commandManager) {
		$cacheDir = $mainManager->getCacheDir();
		$dirs = [$cacheDir, $cacheDir . '/scheduler/'];
		foreach ($dirs as $dir) {
			if (is_readable($dir) && is_writable($dir)) {
				continue;
			}
			$commandManager->run('chmod 777 ' . $dir, true);
		}
		$this->configDirectory = $cacheDir . '/scheduler/';
		$this->schemaManager = $schemaManager;
	}

	/**
	 * Create an archive with scheduler configuration
	 * @return string Path to archive with scheduler configuration
	 */
	public function createArchive(): string {
		try {
			$now = new DateTime();
			$path = '/tmp/iqrf-gateway-scheduler_' . $now->format('c') . '.zip';
		} catch (Throwable $e) {
			$path = '/tmp/iqrf-gateway-scheduler.zip';
		}
		$zipManager = new ZipArchiveManager($path);
		$zipManager->addFolder($this->configDirectory, '');
		if ($zipManager->exist('schema')) {
			$zipManager->deleteDirectory('schema');
		}
		$zipManager->close();
		return $path;
	}

	/**
	 * Downloads a scheduler's configuration
	 * @return FileResponse HTTP response with a scheduler's configuration
	 * @throws BadRequestException
	 */
	public function download(): FileResponse {
		$contentType = 'application/zip';
		$path = $this->createArchive();
		$fileName = basename($path);
		return new FileResponse($path, $fileName, $contentType, true);
	}

	/**
	 * Extracts an archive with scheduler configuration
	 * @param string $path Path to archive with scheduler configuration
	 * @throws JsonException
	 * @throws InvalidTaskMessageException
	 */
	public function extractArchive(string $path): void {
		$zipManager = new ZipArchiveManager($path, ZipArchive::CREATE);
		foreach ($zipManager->listFiles() as $fileName) {
			if (Strings::startsWith($fileName, 'schema/')) {
				continue;
			}
			$json = Json::decode($zipManager->openFile($fileName));
			$this->schemaManager->validate($json);
		}
		if ($zipManager->exist('schema')) {
			$zipManager->deleteDirectory('schema');
		}
		$zipManager->extract($this->configDirectory);
		$zipManager->close();
	}

	/**
	 * Uploads a configuration
	 * @param FileUpload $zip ZIP archive with scheduler configuration
	 * @throws InvalidConfigurationFormatException
	 * @throws InvalidTaskMessageException
	 * @throws JsonException
	 */
	public function upload(FileUpload $zip): void {
		if (!$zip->isOk()) {
			throw new InvalidConfigurationFormatException();
		}
		if ($zip->getContentType() !== 'application/zip') {
			throw new InvalidConfigurationFormatException();
		}
		$this->extractArchive($zip->getTemporaryFile());
	}

}
