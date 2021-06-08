<?php

/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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
use App\GatewayModule\Exceptions\ServiceLogNotAvailableException;
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
		if ($this->commandManager->commandExist('iqrf-gateway-controller')) {
			try {
				$logs['controller'] = $this->getLogFromPath(self::CONTROLLER_LOG);
			} catch (LogNotFoundException $e) {
				$logs['controller'] = null;
			}
		}
		if ($this->commandManager->commandExist('iqrfgd2')) {
			try {
				$logs['daemon'] = $this->getLatestDaemonLog();
			} catch (LogEmptyException $e) {
				$logs['daemon'] = '';
			} catch (LogNotFoundException $e) {
				$logs['daemon'] = null;
			}
		}
		if ($this->commandManager->commandExist('iqrf-gateway-uploader')) {
			try {
				$logs['uploader'] = $this->getLogFromPath(self::UPLOADER_LOG);
			} catch (LogNotFoundException $e) {
				$logs['uploader'] = null;
			}
		}
		$logs['journal'] = $this->getSystemdJournalLog();
		return $logs;
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
				throw new LogEmptyException();
			}
			throw new LogNotFoundException();
		}
		krsort($logFiles);
		return FileSystem::read(reset($logFiles));
	}

	/**
	 * Returns log contents from file specified by parameter
	 * @param string $logFile Log file name
	 * @return string Log file content
	 */
	public function getLogFromPath(string $logFile): string {
		$path = $this->logDir . $logFile;
		if (!file_exists($path)) {
			throw new LogNotFoundException();
		}
		return FileSystem::read($path);
	}

	/**
	 * Returns Systemd Journal log
	 * @return string Systemd Journal log
	 */
	public function getSystemdJournalLog(): string {
		$result = $this->commandManager->run('journalctl --utc --since today --no-pager');
		return $result->getStdout();
	}

	/**
	 * Creates a archive with logs
	 * @return string Path to archive with logs
	 */
	public function createArchive(): string {
		$zipManager = new ZipArchiveManager($this->path);
		if ($this->commandManager->commandExist('iqrf-gateway-controller')) {
			try {
				$zipManager->addFileFromText('iqrf-gateway-controller.log', $this->getLogFromPath(self::CONTROLLER_LOG));
			} catch (LogNotFoundException $e) {
				// not found, do not add
			}
		}
		if ($this->commandManager->commandExist('iqrf-gateway-uploader')) {
			try {
				$zipManager->addFileFromText('iqrf-gateway-uploader.log', $this->getLogFromPath(self::UPLOADER_LOG));
			} catch (LogNotFoundException $e) {
				// not found, do not add
			}
		}
		$zipManager->addFolder($this->daemonLogDir, 'daemon');
		$zipManager->addFileFromText('journal.log', $this->getSystemdJournalLog());
		$zipManager->close();
		return $this->path;
	}

	/**
	 * Returns services with available logs that webapp manages
	 * @return array<int, string> Array of available services and descriptions
	 */
	public function getAvailableServices(): array {
		$services = [];
		if ($this->commandManager->commandExist('iqrf-gateway-controller')) {
			$services[] = 'controller';
		}
		if ($this->commandManager->commandExist('iqrfgd2')) {
			$services[] = 'daemon';
		}
		if ($this->commandManager->commandExist('iqrf-gateway-uploader')) {
			$services[] = 'uploader';
		}
		$services[] = 'journal';
		return $services;
	}

	/**
	 * Returns log of a specified service
	 * @param string $service Service name
	 * @return string Service log
	 */
	public function getServiceLog(string $service): string {
		$services = $this->getAvailableServices();
		if (!in_array($service, $services, true)) {
			throw new ServiceLogNotAvailableException('Service not found');
		}
		if ($service === 'controller') {
			return $this->getLogFromPath(self::CONTROLLER_LOG);
		}
		if ($service === 'daemon') {
			return $this->getLatestDaemonLog();
		}
		if ($service === 'uploader') {
			return $this->getLogFromPath(self::UPLOADER_LOG);
		}
		return $this->getSystemdJournalLog();
	}

}
