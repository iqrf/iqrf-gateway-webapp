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

namespace App\GatewayModule\Models;

use App\CoreModule\Models\ZipArchiveManager;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\FileResponse;
use Nette\SmartObject;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Nette\Utils\Strings;

/**
 * Tool for downloading and reading IQRF Daemon's log files
 */
class LogManager {

	use SmartObject;

	/**
	 * @var string Path to a directory with log files of IQRF Gateway Daemon
	 */
	private $logDir;

	/**
	 * @var string Path to ZIP archive
	 */
	private $path = '/tmp/iqrf-daemon-gateway-logs.zip';

	/**
	 * Constructor
	 * @param string $logDir Path to a directory with log files of IQRF Gateway Daemon
	 */
	public function __construct(string $logDir) {
		$this->logDir = $logDir;
	}

	/**
	 * Load the latest log of IQRF Gateway Daemon
	 * @return string IQRF Gateway Daemon's log
	 */
	public function load(): string {
		$logFiles = $this->getLogFiles();
		return FileSystem::read(reset($logFiles));
	}

	/**
	 * Get IQRF Gateway Daemon's log files
	 * @return array IQRF Gateway Daemon's log files
	 */
	public function getLogFiles(): array {
		$logFiles = [];
		foreach (Finder::findFiles('*iqrf-gateway-daemon.log')->from($this->logDir) as $file) {
			$path = $file->getRealPath();
			$pattern = ['~^' . realpath($this->logDir) . '/~', '/(-iqrf-gateway-daemon|)\.log$/'];
			$fileName = Strings::trim(Strings::replace($path, $pattern, ''), '-');
			$logFiles[$fileName] = $path;
		}
		krsort($logFiles);
		return $logFiles;
	}

	/**
	 * Download logs of IQRF Gateway Daemon
	 * @return FileResponse HTTP response with the logs
	 * @throws BadRequestException
	 */
	public function download(): FileResponse {
		$zipManager = new ZipArchiveManager($this->path);
		$zipManager->addFolder($this->logDir, '');
		$now = new \DateTime();
		$fileName = 'iqrf-gateway-daemon-logs' . $now->format('c') . '.zip';
		$contentType = 'application/zip';
		$zipManager->close();
		$response = new FileResponse($this->path, $fileName, $contentType, true);
		return $response;
	}

}
