<?php

/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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

use App\ConfigModule\Exceptions\InvalidTaskMessageException;
use App\CoreModule\Exceptions\ZipEmptyException;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\ZipArchiveManager;
use DateTime;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Throwable;
use ZipArchive;

/**
 * Scheduler configuration migration manager
 */
class SchedulerMigrationManager {

	/**
	 * @var string Path to a directory with scheduler's configuration
	 */
	private readonly string $configDirectory;

	/**
	 * Constructor
	 * @param MainManager $mainManager Main configuration manager
	 * @param SchedulerSchemaManager $schemaManager Scheduler JSON schema manager
	 * @param CommandManager $commandManager Command manager
	 */
	public function __construct(
		MainManager $mainManager,
		private readonly SchedulerSchemaManager $schemaManager,
		CommandManager $commandManager,
	) {
		$cacheDir = $mainManager->getCacheDir();
		$dirs = [$cacheDir, $cacheDir . 'scheduler/'];
		foreach ($dirs as $dir) {
			if (is_readable($dir) && is_writable($dir)) {
				continue;
			}
			$commandManager->run('chmod 777 ' . escapeshellarg($dir), true);
		}
		$this->configDirectory = $cacheDir . 'scheduler/';
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
		if ($zipManager->exist('schema/')) {
			$zipManager->deleteDirectory('schema');
		}
		if ($zipManager->isEmpty()) {
			throw new ZipEmptyException('No files to export.');
		}
		$zipManager->close();
		return $path;
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
			if (str_starts_with($fileName, 'schema/')) {
				continue;
			}
			$json = Json::decode($zipManager->openFile($fileName));
			$this->schemaManager->validate($json);
		}
		if ($zipManager->exist('schema/')) {
			$zipManager->deleteDirectory('schema');
		}
		$zipManager->extract($this->configDirectory);
		$zipManager->close();
	}

}
