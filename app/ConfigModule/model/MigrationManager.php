<?php

/**
 * Copyright 2018 IQRF Tech s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
declare(strict_types=1);

namespace App\ConfigModule\Model;

use App\ConfigModule\Model\IncompleteConfiguration;
use App\ConfigModule\Model\InvalidConfigurationFormat;
use App\Model\ZipArchiveManager;
use Nette;
use Nette\Application\Responses\FileResponse;
use Nette\Utils\ArrayHash;
use Nette\Utils\FileSystem;

/**
 * Tool for migrationg configuration
 */
class MigrationManager {

	use Nette\SmartObject;

	/**
	 * @var ZipArchiveManager ZIP archive manager
	 */
	private $zipManager;

	/**
	 * @var type string Path to a directory with a configuration of IQRF Gateway Daemon
	 */
	private $configDirectory;

	/**
	 * @var string Path to ZIP archive
	 */
	private $path = '/tmp/iqrf-daemon-configuration.zip';

	/**
	 * Constructor
	 * @param string $configDirectory Path to a directory with a configuration of IQRF Gateway Daemon
	 */
	public function __construct(string $configDirectory) {
		$this->configDirectory = $configDirectory;
		$this->zipManager = new ZipArchiveManager($this->path);
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
	 * @param ArrayHash $formValues Values from form
	 */
	public function upload(ArrayHash $formValues) {
		$zip = $formValues['configuration'];
		if ($zip->isOk()) {
			if ($zip->getContentType() !== 'application/zip') {
				throw new InvalidConfigurationFormat();
			}
			$zip->move($this->path);
			$zipManager = new ZipArchiveManager($this->path, \ZipArchive::CREATE);
			$files = ['BaseService.json', 'config.json', 'iqrfapp.json',
				'IqrfInterface.json', 'JsonSerializer.json', 'MqMessaging.json',
				'MqttMessaging.json', 'Scheduler.json', 'SimpleSerializer.json',
				'TracerFile.json', 'UdpMessaging.json'];
			if (!$zipManager->exist($files)) {
				$zipManager->close();
				FileSystem::delete($this->path);
				throw new IncompleteConfiguration();
			}
			FileSystem::delete($this->configDirectory);
			$zipManager->extract($this->configDirectory);
			$zipManager->close();
			FileSystem::delete($this->path);
		}
	}

}
