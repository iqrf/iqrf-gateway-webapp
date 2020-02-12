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
use App\CoreModule\Models\ZipArchiveManager;
use App\ServiceModule\Exceptions\NotSupportedInitSystemException;
use App\ServiceModule\Models\ServiceManager;
use DateTime;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\FileResponse;
use Nette\Http\FileUpload;
use Nette\SmartObject;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use SplFileInfo;
use ZipArchive;

/**
 * Scheduler configuration migration manager
 */
class SchedulerMigrationManager {

	use SmartObject;

	/**
	 * @var string Path to a directory with scheduler's configuration
	 */
	private $configDirectory;

	/**
	 * @var string Path to ZIP archive
	 */
	private $path = '/tmp/iqrf-daemon-scheduler.zip';

	/**
	 * @var ServiceManager Service manager
	 */
	private $serviceManager;

	/**
	 * Constructor
	 * @param MainManager $mainManager Main configuration manager
	 * @param ServiceManager $serviceManager Service manager
	 */
	public function __construct(MainManager $mainManager, ServiceManager $serviceManager) {
		$this->configDirectory = $mainManager->getCacheDir() . '/scheduler/';
		$this->serviceManager = $serviceManager;
	}

	/**
	 * Downloads a scheduler's configuration
	 * @return FileResponse HTTP response with a scheduler's configuration
	 * @throws BadRequestException
	 */
	public function download(): FileResponse {
		$zipManager = new ZipArchiveManager($this->path);
		$now = new DateTime();
		$fileName = 'iqrf-gateway-scheduler_' . $now->format('c') . '.zip';
		$contentType = 'application/zip';
		$zipManager->addFolder($this->configDirectory, '');
		$zipManager->close();
		return new FileResponse($this->path, $fileName, $contentType, true);
	}

	/**
	 * Uploads a configuration
	 * @param FileUpload $zip ZIP archive with scheduler configuration
	 * @throws InvalidConfigurationFormatException
	 * @throws NotSupportedInitSystemException
	 */
	public function upload(FileUpload $zip): void {
		if (!$zip->isOk()) {
			throw new InvalidConfigurationFormatException();
		}
		if ($zip->getContentType() !== 'application/zip') {
			throw new InvalidConfigurationFormatException();
		}
		$zip->move($this->path);
		$zipManagerUpload = new ZipArchiveManager($this->path, ZipArchive::CREATE);
		$this->removeOldConfiguration();
		$zipManagerUpload->extract($this->configDirectory);
		$zipManagerUpload->close();
		FileSystem::delete($this->path);
		$this->serviceManager->restart();
	}

	/**
	 * Removes an old scheduler's configuration
	 */
	private function removeOldConfiguration(): void {
		/**
		 * @var SplFileInfo $file File info
		 */
		foreach (Finder::findFiles('*.json')->in($this->configDirectory) as $file) {
			FileSystem::delete($file->getPath());
		}
	}

}
