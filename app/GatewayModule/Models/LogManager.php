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

use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\ZipArchiveManager;
use App\GatewayModule\Exceptions\LogEmptyException;
use App\GatewayModule\Exceptions\LogNotFoundException;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use SplFileInfo;

/**
 * Tool for downloading and reading IQRF Gateway Daemon's log files
 */
class LogManager {

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * IQRF Gateway Controller log file name
	 */
	private const CONTROLLER_LOG = 'iqrf-gateway-controller.log';

	/**
	 * IQRF Gateway Uploader log file name
	 */
	private const UPLOADER_LOG = 'iqrf-gateway-uploader.log';

	/**
	 * @var string Path to a directory with log files of IQRF Gateway Daemon
	 */
	private $daemonLogDir;

	/**
	 * @var string Path to a general directory with log files
	 */
	private $logDir;

	/**
	 * @var string Path to ZIP archive
	 */
	private $path = '/tmp/iqrf-gateway-logs.zip';

	/**
	 * Constructor
	 * @param string $logDir Path to a general directory with log files
	 * @param string $daemonLogDir Path to a directory with log files of IQRF Gateway Daemon
	 * @param CommandManager $commandManager Command manager
	 */
	public function __construct(string $logDir, string $daemonLogDir, CommandManager $commandManager) {
		$this->logDir = $logDir;
		$this->daemonLogDir = $daemonLogDir;
		$this->commandManager = $commandManager;
	}

	/**
	 * Loads the latest log of IQRF Gateway Controller and Daemon
	 * @return array<string, string> IQRF Gateway Controller's and Daemon's logs
	 * @throws LogNotFoundException
	 */
	public function load(): array {
		$logs = [];
		try {
			$logs['daemon'] = FileSystem::read($this->getLatestDaemonLog());
		} catch (LogEmptyException $e) {
			$logs['daemon'] = '';
		} catch (LogNotFoundException $e) {
			$logs['daemon'] = null;
		}
		if ($this->commandManager->commandExist('iqrf-gateway-controller')) {
			$logs['controller'] = FileSystem::read($this->getLatestControllerLog());
		}
		return $logs;
	}

	/**
	 * Returns IQRF Gateway Controller's latest log file path
	 */
	public function getLatestControllerLog(): string {
		$logFile = $this->logDir . self::CONTROLLER_LOG;
		if (!file_exists($logFile)) {
			throw new LogNotFoundException('Controller log file not found');
		}
		return $logFile;
	}

	/**
	 * Returns IQRF Gateway Daemon's latest log file path
	 * @return string IQRF Gateway Daemon's latest log file path
	 * @throws LogNotFoundException
	 */
	public function getLatestDaemonLog(): string {
		$logFiles = [];
		$emptyLogFound = false;
		/**
		 * @var SplFileInfo $file File info object
		 */
		foreach (Finder::findFiles('*iqrf-gateway-daemon.log')->from($this->daemonLogDir) as $file) {
			if ($file->getSize() === 0) {
				$emptyLogFound = true;
				continue;
			}
			$logFiles[$file->getMTime()] = $file->getRealPath();
		}
		if ($logFiles === []) {
			if ($emptyLogFound) {
				throw new LogEmptyException('Daemon log file is empty.');
			}
			throw new LogNotFoundException('Daemon log files not found');
		}
		krsort($logFiles);
		return reset($logFiles);
	}

	/**
	 * Creates a archive with logs
	 * @return string Path to archive with logs
	 */
	public function createArchive(): string {
		$zipManager = new ZipArchiveManager($this->path);
		if ($this->commandManager->commandExist('iqrf-gateway-controller')) {
			$zipManager->addFolder($this->logDir . 'iqrf-gateway-controller', 'controller');
			$zipManager->addFile($this->logDir . self::CONTROLLER_LOG, 'controller/' . self::CONTROLLER_LOG);
		}
		if ($this->commandManager->commandExist('iqrf-gateway-uploader')) {
			$zipManager->addFolder($this->logDir . 'iqrf-gateway-uploader', 'uploader');
			$zipManager->addFile($this->logDir . self::UPLOADER_LOG, 'uploader/' . self::UPLOADER_LOG);
		}
		$zipManager->addFolder($this->daemonLogDir, 'daemon');
		$zipManager->close();
		return $this->path;
	}

}
