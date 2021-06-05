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

use App\ConfigModule\Models\MainManager;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\ZipArchiveManager;
use App\GatewayModule\Models\Utils\GatewayInfoUtil;
use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use DateTime;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;
use Throwable;

/**
 * Gateway diagnostics tool
 */
class DiagnosticsManager {

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * @var GatewayInfoUtil Gateway info manager
	 */
	private $gwInfo;

	/**
	 * @var InfoManager Gateway info manager
	 */
	private $infoManager;

	/**
	 * @var ZipArchiveManager ZIP archive manager
	 */
	private $zipManager;

	/**
	 * @var string Path to a directory with IQRF Gateway Daemon's cache
	 */
	private $cacheDir;

	/**
	 * @var string Path to a directory with IQRF Gateway Daemon's configuration
	 */
	private $confDir;

	/**
	 * @var string Path to a directory with IQRF Gateway Daemon's data
	 */
	private $dataDir;

	/**
	 * @var string Path to a directory with log files of IQRF Gateway Daemon
	 */
	private $logDir;

	/**
	 * Constructor
	 * @param string $confDir Path to a directory with IQRF Gateway Daemon's configuration
	 * @param string $logDir Path to a directory with log files of IQRF Gateway Daemon
	 * @param CommandManager $commandManager Command manager
	 * @param InfoManager $infoManager Gateway Info manager
	 * @param MainManager $mainManager Main configuration manager
	 */
	public function __construct(string $confDir, string $logDir, CommandManager $commandManager, InfoManager $infoManager, MainManager $mainManager, GatewayInfoUtil $gwInfo) {
		$this->commandManager = $commandManager;
		$this->infoManager = $infoManager;
		$this->cacheDir = $mainManager->getCacheDir();
		$this->confDir = $confDir;
		$this->dataDir = $mainManager->getDataDir();
		$this->logDir = $logDir;
		$this->gwInfo = $gwInfo;
	}

	/**
	 * Create an archive with diagnostics data
	 * @return string Path to archive with diagnostics data
	 */
	public function createArchive(): string {
		try {
			$now = new DateTime();
			$gwId = $this->gwInfo->getProperty('gwId');
			$gwId = $gwId === null ? '' : strtolower($gwId) . '_';
			$path = '/tmp/iqrf-gateway-diagnostics_' . $gwId . $now->format('c') . '.zip';
		} catch (Throwable $e) {
			$path = '/tmp/iqrf-gateway-diagnostics.zip';
		}
		$this->zipManager = new ZipArchiveManager($path);
		$this->addConfiguration();
		$this->addDatabase();
		$this->addMetadata();
		$this->addScheduler();
		$this->addDaemonLog();
		$this->addDmesg();
		$this->addInfo();
		$this->addServices();
		$this->addSpi();
		$this->addUsb();
		$this->addControllerLog();
		$this->addUploaderLog();
		$this->addWebappLog();
		$this->addInstalledPackages();
		$this->zipManager->close();
		return $path;
	}

	/**
	 * Adds a configuration of IQRF Gateway Daemon
	 */
	public function addConfiguration(): void {
		$this->zipManager->addFolder($this->confDir, 'configuration');
	}

	/**
	 * Adds IQRF Gateway Daemon database and scripts
	 */
	public function addDatabase(): void {
		$this->zipManager->addFolder($this->dataDir . '/DB', 'DB');
	}

	/**
	 * Adds IQRF Gateway Daemon's metadata
	 */
	public function addMetadata(): void {
		$this->zipManager->addFolder($this->cacheDir . '/metaData', 'metaData');
	}

	/**
	 * Adds a configuration of IQRF Gateway Daemon's scheduler
	 */
	public function addScheduler(): void {
		$this->zipManager->addFolder($this->cacheDir . '/scheduler', 'scheduler');
		if ($this->zipManager->exist('scheduler/schema/')) {
			$this->zipManager->deleteDirectory('scheduler/schema');
		}
	}

	/**
	 * Adds logs of IQRF Gateway Daemon
	 */
	public function addDaemonLog(): void {
		$this->zipManager->addFolder($this->logDir, 'logs/iqrf-gateway-daemon');
	}

	/**
	 * Adds information from dmesg command
	 */
	public function addDmesg(): void {
		$output = $this->commandManager->run('dmesg', true)->getStdout();
		$this->zipManager->addFileFromText('dmesg.log', $output);
	}

	/**
	 * Adds basic information about the gateway
	 */
	public function addInfo(): void {
		$array = $this->infoManager->get();
		try {
			$array['coordinator'] = $this->infoManager->getCoordinatorInfo();
		} catch (DpaErrorException | EmptyResponseException | JsonException $e) {
			$array['coordinator'] = null;
		}
		$array['uname'] = $this->commandManager->run('uname -a', true)->getStdout();
		$array['uptime'] = $this->commandManager->run('uptime -p', true)->getStdout();
		try {
			$this->zipManager->addJsonFromArray('info.json', $array);
		} catch (JsonException $e) {
			return;
		}
	}

	/**
	 * Adds information about services
	 */
	public function addServices(): void {
		if ($this->commandManager->commandExist('systemctl')) {
			$output = $this->commandManager->run('systemctl list-units --type=service', true)->getStdout();
			$this->zipManager->addFileFromText('services.log', $output);
		}
	}

	/**
	 * Adds information about available SPI interfaces
	 */
	public function addSpi(): void {
		$output = $this->commandManager->run('ls /dev/spidev*', true)->getStdout();
		if ($output !== '') {
			$this->zipManager->addFileFromText('spidev.log', $output);
		}
	}

	/**
	 * Adds information from lsusb about USB gateways and programmers
	 */
	public function addUsb(): void {
		if (!$this->commandManager->commandExist('lsusb')) {
			return;
		}
		$output = $this->commandManager->run('lsusb -v -d 1de6:', true)->getStdout();
		if ($output !== '') {
			$this->zipManager->addFileFromText('lsusb.log', $output);
		}
	}

	/**
	 * Adds logs of IQRF Gateway Controller
	 */
	public function addControllerLog(): void {
		$command = $this->commandManager->run('journalctl --unit iqrf-gateway-controller.service --no-pager', true);
		if ($command->getExitCode() === 0) {
			$this->zipManager->addFileFromText('logs/iqrf-gateway-controller.log', $command->getStdout());
		}
	}

	/**
	 * Adds logs of IQRF Gateway Uploader
	 */
	public function addUploaderLog(): void {
		if ($this->commandManager->commandExist('iqrf-gateway-uploader')) {
			if (file_exists('/var/log/iqrf-gateway-uploader.log')) {
				$this->zipManager->addFile('/var/log/iqrf-gateway-uploader.log', 'logs/iqrf-gateway-uploader.log');
			}
		}
	}

	/**
	 * Adds logs of IQRF Gateway Webapp
	 */
	public function addWebappLog(): void {
		$logDir = __DIR__ . '/../../../log/';
		$this->zipManager->addFolder($logDir, 'logs/iqrf-gateway-webapp');
	}

	/**
	 * Adds list of installed packages
	 */
	public function addInstalledPackages(): void {
		if ($this->commandManager->commandExist('apt')) {
			$command = $this->commandManager->run('apt list --installed', true);
			$packages = Strings::replace($command->getStdout(), '/Listing...\n/');
			$this->zipManager->addFileFromText('installed_packages.txt', $packages);
		}
	}

}
