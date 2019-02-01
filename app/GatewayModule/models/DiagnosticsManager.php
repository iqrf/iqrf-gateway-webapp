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

use App\ConfigModule\Models\MainManager;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\ZipArchiveManager;
use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use DateTime;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\FileResponse;
use Nette\SmartObject;
use Nette\Utils\JsonException;

/**
 * Gateway diagnostics tool
 */
class DiagnosticsManager {

	use SmartObject;

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

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
	 * @var string Path to a directory with log files of IQRF Gateway Daemon
	 */
	private $logDir;

	/**
	 * @var string Path to ZIP archive
	 */
	private $path = '/tmp/iqrf-gateway-webapp.zip';

	/**
	 * Constructor
	 * @param string $confDir Path to a directory with IQRF Gateway Daemon's configuration
	 * @param string $logDir Path to a directory with log files of IQRF Gateway Daemon
	 * @param CommandManager $commandManager Command manager
	 * @param InfoManager $infoManager Gateway Info manager
	 * @param MainManager $mainManager Main configuration manager
	 */
	public function __construct(string $confDir, string $logDir, CommandManager $commandManager, InfoManager $infoManager, MainManager $mainManager) {
		$this->commandManager = $commandManager;
		$this->infoManager = $infoManager;
		try {
			$this->cacheDir = $mainManager->load()['cacheDir'];
		} catch (JsonException $e) {
			$this->cacheDir = '/var/cache/iqrf-gateway-daemon/';
		}
		$this->confDir = $confDir;
		$this->logDir = $logDir;
	}

	/**
	 * Download a diagnostic data
	 * @return FileResponse HTTP response with the diagnostic data
	 * @throws BadRequestException
	 * @throws JsonException
	 */
	public function download(): FileResponse {
		$this->zipManager = new ZipArchiveManager($this->path);
		$now = new DateTime();
		$fileName = 'iqrf-gateway-diagnostics_' . $now->format('c') . '.zip';
		$contentType = 'application/zip';
		$this->addConfiguration();
		$this->addMetadata();
		$this->addScheduler();
		$this->addDaemonLog();
		$this->addDmesg();
		$this->addInfo();
		$this->addServices();
		$this->addSpi();
		$this->addUsb();
		$this->addWebappLog();
		$this->zipManager->close();
		$response = new FileResponse($this->path, $fileName, $contentType, true);
		return $response;
	}

	/**
	 * Add configuration of IQRF Gateway Daemon
	 */
	public function addConfiguration(): void {
		$this->zipManager->addFolder($this->confDir, 'configuration');
	}

	/**
	 * Add IQRF Gateway Daemon's metadata
	 */
	public function addMetadata(): void {
		$this->zipManager->addFolder($this->cacheDir . '/metaData', 'metaData');
	}

	/**
	 * Add scheduler's configuration
	 */
	public function addScheduler(): void {
		$this->zipManager->addFolder($this->cacheDir . '/scheduler', 'scheduler');
	}

	/**
	 * Add log of IQRF Gateway daemon
	 */
	public function addDaemonLog(): void {
		$this->zipManager->addFolder($this->logDir, 'logs/iqrf-gateway-daemon');
	}

	/**
	 * Add information from dmesg command
	 */
	public function addDmesg(): void {
		$output = $this->commandManager->send('dmesg', true);
		$this->zipManager->addFileFromText('dmesg.log', $output);
	}

	/**
	 * Add basic information about the gateway
	 * @throws JsonException
	 */
	public function addInfo(): void {
		$array = [];
		$array['board'] = $this->infoManager->getBoard();
		$array['daemonVersion'] = $this->infoManager->getDaemonVersion();
		$array['webappVersion'] = $this->infoManager->getWebAppVersion();
		try {
			$array['coordinator'] = $this->infoManager->getCoordinatorInfo();
		} catch (DpaErrorException | EmptyResponseException $e) {
			$array['coordinator'] = 'ERROR';
		}
		$array['hostname'] = $this->infoManager->getHostname();
		$array['uname'] = $this->commandManager->send('uname -a', true);
		$array['uptime'] = $this->commandManager->send('uptime -p', true);
		$array['diskUsages'] = $this->infoManager->getDiskUsages();
		$array['memoryUsage'] = $this->infoManager->getMemoryUsage();
		$array['swapUsage'] = $this->infoManager->getSwapUsage();
		$this->zipManager->addJsonFromArray('info.json', $array);
	}

	/**
	 * Add information about services
	 */
	public function addServices(): void {
		if ($this->commandManager->commandExist('systemctl')) {
			$output = $this->commandManager->send('systemctl list-units --type=service', true);
			$this->zipManager->addFileFromText('services.log', $output);
		}
	}

	/**
	 * Add information about available SPI interfaces
	 */
	public function addSpi(): void {
		$output = $this->commandManager->send('ls /dev/spidev*', true);
		if ($output !== '') {
			$this->zipManager->addFileFromText('spidev.log', $output);
		}
	}

	/**
	 * Add information from lsusb about USB gateways and programmers
	 */
	public function addUsb(): void {
		if ($this->commandManager->commandExist('lsusb')) {
			$output = $this->commandManager->send('lsusb -v -d 1de6:', true);
			if ($output !== '') {
				$this->zipManager->addFileFromText('lsusb.log', $output);
			}
		}
	}

	/**
	 * Add log of IQRF Gateway daemon webapp
	 */
	public function addWebappLog(): void {
		$logDir = __DIR__ . '/../../../log/';
		$this->zipManager->addFolder($logDir, 'logs/iqrf-gateway-webapp');
	}

}
