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

namespace App\GatewayModule\Models;

use App\CoreModule\Models\ZipArchiveManager;
use DateTime;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\FileResponse;
use Nette\SmartObject;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Nette\Utils\Strings;
use SplFileInfo;

/**
 * Tool for downloading and reading IQRF Gateway Daemon's log files
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
	 * Loads the latest log of IQRF Gateway Daemon
	 * @return string IQRF Gateway Daemon's log
	 */
	public function load(): string {
		return FileSystem::read($this->getLatestLog());
	}

	/**
	 * Returns IQRF Gateway Daemon's latest log file path
	 * @return string IQRF Gateway Daemon's latest log file path
	 */
	public function getLatestLog(): string {
		$logFiles = [];
		/**
		 * @var SplFileInfo $file File info object
		 */
		foreach (Finder::findFiles('*iqrf-gateway-daemon.log')->from($this->logDir) as $file) {
			if ($file->getSize() === 0) {
				continue;
			}
			$logFiles[$file->getMTime()] = $file->getRealPath();
		}
		krsort($logFiles);
		return reset($logFiles);
	}

	/**
	 * Downloads logs of IQRF Gateway Daemon
	 * @return FileResponse HTTP response with the logs
	 * @throws BadRequestException
	 */
	public function download(): FileResponse {
		$zipManager = new ZipArchiveManager($this->path);
		$zipManager->addFolder($this->logDir, '');
		$now = new DateTime();
		$fileName = 'iqrf-gateway-daemon-logs' . $now->format('c') . '.zip';
		$contentType = 'application/zip';
		$zipManager->close();
		return new FileResponse($this->path, $fileName, $contentType, true);
	}

}
