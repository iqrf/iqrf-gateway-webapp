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

namespace App\GatewayModule\Model;

use App\IqrfAppModule\Model\IqrfAppManager;
use App\CoreModule\Model\CommandManager;
use App\CoreModule\Model\JsonFileManager;
use App\CoreModule\Model\VersionManager;
use Nette;

/**
 * Tool for getting information about this gateway
 */
class InfoManager {

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
	 * @var JsonFileManager JSON file manager
	 */
	private $jsonFileManager;

	/**
	 * @var VersionManager version manager
	 */
	private $versionManager;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 * @param IqrfAppManager $iqrfAppManager IqrfApp manager
	 * @param VersionManager $versionManager Version manager
	 */
	public function __construct(CommandManager $commandManager, IqrfAppManager $iqrfAppManager, VersionManager $versionManager) {
		$this->commandManager = $commandManager;
		$this->iqrfAppManager = $iqrfAppManager;
		$this->jsonFileManager = new JsonFileManager(__DIR__ . '/../../../');
		$this->versionManager = $versionManager;
	}

	/**
	 * Get board's vendor, name and version
	 * @return string Board's vendor, name and version
	 */
	public function getBoard() {
		$deviceTree = $this->commandManager->send('cat /proc/device-tree/model', true);
		if (!empty($deviceTree)) {
			return $deviceTree;
		}
		$dmiBoardVendor = $this->commandManager->send('cat /sys/class/dmi/id/board_vendor', true);
		$dmiBoardName = $this->commandManager->send('cat /sys/class/dmi/id/board_name', true);
		$dmiBoardVersion = $this->commandManager->send('cat /sys/class/dmi/id/board_version', true);
		if (!empty($dmiBoardName) && !empty($dmiBoardVendor) && !empty($dmiBoardVersion)) {
			return $dmiBoardVendor . ' ' . $dmiBoardName . ' (' . $dmiBoardVersion . ')';
		}
		return 'UNKNOWN';
	}

	/**
	 * Get IPv4 and IPv6 addresses of the gateway
	 * @return array IPv4 and IPv6 addresses
	 */
	public function getIpAddresses() {
		$addresses = [];
		$lsInterfaces = $this->commandManager->send('ls /sys/class/net | awk \'{ print $0 }\'', true);
		$interfaces = explode(PHP_EOL, $lsInterfaces);
		foreach ($interfaces as $interface) {
			if ($interface === 'lo') {
				continue;
			}
			$cmd = 'ip a s ' . $interface . ' | grep inet | grep global | grep -v temporary | awk \'{print $2}\'';
			$output = $this->commandManager->send($cmd, true);
			if (!empty($output)) {
				$addresses[$interface] = explode(PHP_EOL, $output);
			}
		}
		return $addresses;
	}

	/**
	 * Get MAC addresses of the gateway
	 * @return array MAC addresses array
	 */
	public function getMacAddresses() {
		$addresses = [];
		$lsInterfaces = $this->commandManager->send('ls /sys/class/net | awk \'{ print $0 }\'', true);
		$interfaces = explode(PHP_EOL, $lsInterfaces);
		foreach ($interfaces as $interface) {
			if ($interface === 'lo') {
				continue;
			}
			$cmd = 'cat /sys/class/net/' . $interface . '/address';
			$addresses[$interface] = $this->commandManager->send($cmd, true);
		}
		return $addresses;
	}

	/**
	 * Get version of the daemon
	 * @return string IQRF Daemon version
	 */
	public function getDaemonVersion() {
		$cmd = 'iqrfgd2 version';
		$daemonExistence = $this->commandManager->commandExist('iqrfgd2');
		if (!$daemonExistence) {
			return 'none';
		}
		$result = $this->commandManager->send($cmd);
		if (!empty($result)) {
			return $result;
		}
		return 'unknown';
	}

	/**
	 * Get hostname of the gateway
	 * @return string Hostname
	 */
	public function getHostname() {
		$cmd = 'hostname -f';
		return $this->commandManager->send($cmd);
	}

	/**
	 * Get information about the Coordinator
	 * @return array Information about the Coordinator
	 */
	public function getCoordinatorInfo() {
		$response = $this->iqrfAppManager->sendRaw('00.00.02.00.FF.FF');
		return $this->iqrfAppManager->parseResponse($response);
	}

	/**
	 * Get current version of this wab application
	 * @return string Version of this web application
	 */
	public function getWebAppVersion() {
		return $this->versionManager->getInstalledWebapp();
	}

}
