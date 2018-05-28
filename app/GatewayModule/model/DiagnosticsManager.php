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

namespace App\GatewayModule\Model;

use App\IqrfAppModule\Model\IqrfAppManager;
use App\GatewayModule\Model\InfoManager;
use App\Model\CommandManager;
use App\Model\ZipArchiveManager;
use Nette;
use Nette\Application\Responses\FileResponse;

/**
 * Tool for getting information about this gateway
 */
class DiagnosticsManager {

	use Nette\SmartObject;

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * @var IqrfAppManager IqrfApp manager
	 */
	private $iqrfAppManager;

	/**
	 * @var InfoManager Gateway info manager
	 */
	private $infoManager;

	/**
	 * @var ZipArchiveManager ZIP archive manager
	 */
	private $zipManager;

	/**
	 * @var string Path to ZIP archive
	 */
	private $path = '/tmp/iqrf-daemon-webapp.zip';

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 * @param IqrfAppManager $iqrfAppManager IqrfApp manager
	 */
	public function __construct(CommandManager $commandManager, IqrfAppManager $iqrfAppManager, InfoManager $infoManager) {
		$this->commandManager = $commandManager;
		$this->iqrfAppManager = $iqrfAppManager;
		$this->infoManager = $infoManager;
		$this->zipManager = new ZipArchiveManager($this->path);
	}

	public function addInfo() {
		$array = [];
		$array['board'] = $this->infoManager->getBoard();
		$array['daemonVersion'] = $this->infoManager->getDaemonVersion();
		$array['webappVersion'] = $this->infoManager->getWebAppVersion();
		$array['coordinator'] = $this->infoManager->getCoordinatorInfo();
		$array['hostname'] = $this->infoManager->getHostname();
		$array['uname'] = $this->commandManager->send('uname -a', true);
		$array['uptime'] = $this->commandManager->send('uptime -p', true);
		$this->zipManager->addJsonFromArray('info.json', $array);
	}

	/**
	 * Add log of IQRF Gateway daemon
	 */
	public function addDaemonLog() {
		$this->zipManager->addFile('/var/log/iqrf-daemon.log', 'iqrf-daemon.log');
	}

	/**
	 * Add information from dmesg commmand
	 */
	public function addDmesg() {
		$output = $this->commandManager->send('dmesg', true);
		$this->zipManager->addFileFromText('dmesg.log', $output);
	}

	/**
	 * Add information about services
	 */
	public function addServices() {
		if ($this->commandManager->commandExist('systemctl')) {
			$output = $this->commandManager->send('systemctl list-units --type=service', true);
			$this->zipManager->addFileFromText('services.log', $output);
		}
	}

	/**
	 * Add information about available SPI interfaces
	 */
	public function addSpi() {
		$output = $this->commandManager->send('ls /dev/spidev*', true);
		$this->zipManager->addFileFromText('spidev.log', $output);
	}

	/**
	 * Add information from lsusb about USB gateways and programmers
	 */
	public function addUsb() {
		if ($this->commandManager->commandExist('lsusb')) {
			$output = $this->commandManager->send('lsusb -v -d 1de6:', true);
			$this->zipManager->addFileFromText('lsusb.log', $output);
		}
	}

	/**
	 * Download a diagnostic data
	 * @return FileResponse HTTP response with the diagnostic data
	 */
	public function download() {
		$fileName = 'iqrf-gateway-diagnostics.zip';
		$contentType = 'application/zip';
		$this->addDaemonLog();
		$this->addDmesg();
		$this->addInfo();
		$this->addServices();
		$this->addSpi();
		$this->addUsb();
		$this->zipManager->close();
		$response = new FileResponse($this->path, $fileName, $contentType, true);
		return $response;
	}

}
