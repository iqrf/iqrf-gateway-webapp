<?php

/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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
use App\GatewayModule\Models\Utils\GatewayInfoUtil;
use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Models\EnumerationManager;
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
	private CommandManager $commandManager;

	/**
	 * @var DaemonDirectories IQRF Gateway Daemon's directory manager
	 */
	private DaemonDirectories $daemonDirectories;

	/**
	 * @var EnumerationManager IQMESH Enumeration manager
	 */
	private EnumerationManager $enumerationManager;

	/**
	 * @var GatewayInfoUtil Gateway info manager
	 */
	private GatewayInfoUtil $gwInfo;

	/**
	 * @var InfoManager Gateway info manager
	 */
	private InfoManager $infoManager;

	/**
	 * @var ZipArchiveManager ZIP archive manager
	 */
	private ZipArchiveManager $zipManager;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 * @param DaemonDirectories $daemonDirectories IQRF Gateway Daemon's directory manager
	 * @param EnumerationManager $enumerationManager IQMESH Enumeration manager
	 * @param InfoManager $infoManager Gateway Info manager
	 * @param GatewayInfoUtil $gwInfo Gateway information file manager
	 */
	public function __construct(CommandManager $commandManager, DaemonDirectories $daemonDirectories, EnumerationManager $enumerationManager, InfoManager $infoManager, GatewayInfoUtil $gwInfo) {
		$this->commandManager = $commandManager;
		$this->daemonDirectories = $daemonDirectories;
		$this->enumerationManager = $enumerationManager;
		$this->infoManager = $infoManager;
		$this->gwInfo = $gwInfo;
	}

	/**
	 * Create an archive with diagnostics data
	 * @return string Path to archive with diagnostics data
	 */
	public function createArchive(): string {
		try {
			$date = new DateTime();
			$gwId = $this->gwInfo->getId();
			$gwId = strtolower($gwId) . '_';
			$path = sprintf('/tmp/iqrf-gateway-diagnostics_%s_%s.zip', strtolower($gwId), $date->format('c'));
		} catch (Throwable $e) {
			$path = '/tmp/iqrf-gateway-diagnostics.zip';
		}
		$this->zipManager = new ZipArchiveManager($path);
		$this->addConfiguration();
		$this->addDatabase();
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
		$this->addJournalLog();
		$this->addSyslog();
		$this->addInstalledPackages();
		$this->addProcesses();
		$this->addTuptime();
		$this->zipManager->close();
		$this->cleanup();
		return $path;
	}

	/**
	 * Adds a configuration of IQRF Gateway Daemon
	 */
	public function addConfiguration(): void {
		$this->zipManager->addFolder($this->daemonDirectories->getConfigurationDir(), 'configuration');
	}

	/**
	 * Adds IQRF Gateway Daemon database and scripts
	 */
	public function addDatabase(): void {
		$this->zipManager->addFolder($this->daemonDirectories->getDataDir() . 'DB', 'DB');
	}

	/**
	 * Adds a configuration of IQRF Gateway Daemon's scheduler
	 */
	public function addScheduler(): void {
		$this->zipManager->addFolder($this->daemonDirectories->getCacheDir() . 'scheduler', 'scheduler');
		if ($this->zipManager->exist('scheduler/schema/')) {
			$this->zipManager->deleteDirectory('scheduler/schema');
		}
	}

	/**
	 * Adds logs of IQRF Gateway Daemon
	 */
	public function addDaemonLog(): void {
		$this->zipManager->addFolder($this->daemonDirectories->getLogDir(), 'logs/iqrf-gateway-daemon');
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
			$array['coordinator'] = $this->enumerationManager->device(0);
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
		if ($this->commandManager->commandExist('iqrf-gateway-uploader') &&
			file_exists('/var/log/iqrf-gateway-uploader.log')) {
			$this->zipManager->addFile('/var/log/iqrf-gateway-uploader.log', 'logs/iqrf-gateway-uploader.log');
		}
	}

	/**
	 * Adds logs of IQRF Gateway Webapp
	 */
	public function addWebappLog(): void {
		$logDir = __DIR__ . '/../../../log/';
		if (file_exists($logDir)) {
			$this->zipManager->addFolder($logDir, 'logs/iqrf-gateway-webapp');
		}
	}

	/**
	 * Adds logs of Systemd journal
	 */
	public function addJournalLog(): void {
		$command = $this->commandManager->run('journalctl --utc --no-pager', true);
		$this->zipManager->addFileFromText('logs/journal.log', $command->getStdout());
	}

	/**
	 * Adds syslog files
	 */
	public function addSyslog(): void {
		$product = $this->gwInfo->getImage();
		if (Strings::contains($product, 'armbian')) {
				$this->commandManager->run('mkdir -p /tmp/syslog/log.hdd/', true);
				$this->commandManager->run('cp /var/log.hdd/syslog* /tmp/syslog/log.hdd/', true);
		}
		$this->commandManager->run('mkdir -p /tmp/syslog/', true);
		$this->commandManager->run('cp /var/log/syslog* /tmp/syslog/', true);
		$this->commandManager->run('find /tmp/syslog -type d -exec chmod 777 {} \;', true);
		$this->commandManager->run('find /tmp/syslog -type f -exec chmod 666 {} \;', true);
		$this->zipManager->addFolder('/tmp/syslog', 'syslog');
	}

	/**
	 * Adds list of installed packages
	 */
	public function addInstalledPackages(): void {
		if ($this->commandManager->commandExist('apt')) {
			$command = $this->commandManager->run('apt list --installed', true);
			$packages = Strings::replace($command->getStdout(), '#Listing...\n#');
			$this->zipManager->addFileFromText('installed_packages.txt', $packages);
		}
	}

	/**
	 * Adds process info
	 */
	public function addProcesses(): void {
		if ($this->commandManager->commandExist('ps')) {
			$output = $this->commandManager->run('ps -axeu', true)->getStdout();
			$this->zipManager->addFileFromText('processes.txt', $output);
		}
	}

	/**
	 * Adds tuptime (startup/shutdown/downtime) info
	 */
	public function addTuptime(): void {
		if ($this->commandManager->commandExist('tuptime')) {
			$output = $this->commandManager->run('tuptime -kpt')->getStdout();
			$this->zipManager->addFileFromText('tuptime.txt', $output);
		}
	}

	/**
	 * Cleans up auxiliary directories
	 */
	public function cleanup(): void {
		$this->commandManager->run('rm -rf /tmp/syslog', true);
	}

}
