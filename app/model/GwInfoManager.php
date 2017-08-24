<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017 IQRF Tech s.r.o.
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

namespace App\Model;

use App\IqrfAppModule\Model\IqrfAppManager;
use App\Model\CommandManager;
use Nette;

class GwInfoManager {

	use Nette\SmartObject;

	/**
	 * @var CommandManager
	 */
	private $commandManager;

	/**
	 * @var IqrfAppManager
	 */
	private $iqrfAppManager;

	/**
	 * Constructor
	 * @param CommandManager $commandManager
	 * @param IqrfAppManager $iqrfAppManager
	 */
	public function __construct(CommandManager $commandManager, IqrfAppManager $iqrfAppManager) {
		$this->commandManager = $commandManager;
		$this->iqrfAppManager = $iqrfAppManager;
	}

	/**
	 * Get IPv4 and IPv6 addresses of the gateway
	 * @return array IPv4 and IPv6 addresses
	 */
	public function getIpAddresses() {
		$addresses = [];
		$lsInterfaces = $this->commandManager->send("ls /sys/class/net | awk '{ print $0 }'", true);
		$interfaces = explode(PHP_EOL, $lsInterfaces);
		foreach ($interfaces as $interface) {
			if ($interface !== 'lo') {
				$cmd = 'ip a s ' . $interface . ' | grep inet | grep global | grep -v temporary | awk \'{print $2}\'';
				$output = $this->commandManager->send($cmd, true);
				if (!empty($output)) {
					$addresses[$interface] = explode(PHP_EOL, $output);
				}
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
		$lsInterfaces = $this->commandManager->send("ls /sys/class/net | awk '{ print $0 }'", true);
		$interfaces = explode(PHP_EOL, $lsInterfaces);
		foreach ($interfaces as $interface) {
			if ($interface !== 'lo') {
				$cmd = 'cat /sys/class/net/' . $interface . '/address';
				$addresses[$interface] = $this->commandManager->send($cmd, true);
			}
		}
		return $addresses;
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

}
