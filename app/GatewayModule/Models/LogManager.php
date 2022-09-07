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
	private CommandManager $commandManager;

	/**
	 * @var string IQRF Gateway Controller name
	 */
	public const CONTROLLER = 'iqrf-gateway-controller';

	/**
	 * @var string IQRF Gateway Daemon name
	 */
	public const DAEMON = 'iqrf-gateway-daemon';

	/**
	 * @var string IQRF Gateway Setter name
	 */
	public const SETTER = 'iqrf-gateway-setter';

	/**
	 * @var string IQRF Gateway Translator name
	 */
	public const TRANSLATOR = 'iqrf-gateway-translator';

	/**
	 * @var string IQRF Gateway Uploader name
	 */
	public const UPLOADER = 'iqrf-gateway-uploader';

	/**
	 * @ string IQRF Gateway Controller log file
	 */
	private const CONTROLLER_LOG = 'iqrf-gateway-controller.log';

	/**
	 * @var string IQRF Gateway Setter log file
	 */
	private const SETTER_LOG = 'iqrf-gateway-setter.log';

	/**
	 * @var string IQRF Gateway Translator log file
	 */
	private const TRANSLATOR_LOG = 'iqrf-gateway-translator.log';

	/**
	 * @var string IQRF Gateway Uploader log file
	 */
	private const UPLOADER_LOG = 'iqrf-gateway-uploader.log';

	/**
	 * @var string Path to a directory with log files of IQRF Gateway Daemon
	 */
	private string $daemonLogDir;

	/**
	 * @var string Path to a general directory with log files
	 */
	private string $logDir;

	/**
	 * @var string Path to ZIP archive
	 */
	private string $path = '/tmp/iqrf-gateway-logs.zip';

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
			if (!$emptyLogFound) {
				throw new LogNotFoundException();
			}
			return '';
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
	 * Returns Journal records
	 * @return string Journal records
	 */
	public function getJournal(): string {
		$result = $this->commandManager->run('journalctl --utc -b --no-pager', true);
		return $result->getStdout();
	}

	/**
	 * Creates a archive with logs
	 * @return string Path to archive with logs
	 */
	public function createArchive(): string {
		$zipManager = new ZipArchiveManager($this->path);
		if ($this->commandManager->commandExist(self::CONTROLLER)) {
			try {
				$zipManager->addFileFromText(self::CONTROLLER_LOG, $this->getLogFromPath(self::CONTROLLER_LOG));
			} catch (LogNotFoundException $e) {
				// not found, do not add
			}
		}
		if ($this->commandManager->commandExist(self::SETTER)) {
			try {
				$zipManager->addFileFromText(self::SETTER_LOG, $this->getLogFromPath(self::SETTER_LOG));
			} catch (LogNotFoundException $e) {
				// not found, do not add
			}
		}
		if ($this->commandManager->commandExist(self::TRANSLATOR)) {
			try {
				$zipManager->addFileFromText(self::TRANSLATOR_LOG, $this->getLogFromPath(self::TRANSLATOR_LOG));
			} catch (LogNotFoundException $e) {
				// not found, do not add
			}
		}
		if ($this->commandManager->commandExist(self::UPLOADER)) {
			try {
				$zipManager->addFileFromText(self::UPLOADER_LOG, $this->getLogFromPath(self::UPLOADER_LOG));
			} catch (LogNotFoundException $e) {
				// not found, do not add
			}
		}
		$zipManager->addFolder($this->daemonLogDir, 'daemon');
		$zipManager->addFileFromText('journal.log', $this->getJournal());
		$zipManager->close();
		return $this->path;
	}

	/**
	 * Returns services with available logs that webapp manages
	 * @return array<int, string> Array of available services and descriptions
	 */
	public function getAvailableServices(): array {
		$services = [];
		if ($this->commandManager->commandExist(self::CONTROLLER)) {
			$services[] = self::CONTROLLER;
		}
		if ($this->commandManager->commandExist('iqrfgd2')) {
			$services[] = self::DAEMON;
		}
		if ($this->commandManager->commandExist(self::SETTER)) {
			$services[] = self::SETTER;
		}
		if ($this->commandManager->commandExist(self::TRANSLATOR)) {
			$services[] = self::TRANSLATOR;
		}
		if ($this->commandManager->commandExist(self::UPLOADER)) {
			$services[] = self::UPLOADER;
		}
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
		if ($service === self::CONTROLLER) {
			return $this->getLogFromPath(self::CONTROLLER_LOG);
		}
		if ($service === self::DAEMON) {
			return $this->getLatestDaemonLog();
		}
		if ($service === self::SETTER) {
			return $this->getLogFromPath(self::SETTER_LOG);
		}
		if ($service === self::TRANSLATOR) {
			return $this->getLogFromPath(self::TRANSLATOR_LOG);
		}
		return $this->getLogFromPath(self::UPLOADER_LOG);
	}

}
