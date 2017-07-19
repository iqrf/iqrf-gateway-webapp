<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
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

use App\Model\CommandManager;
use Nette;

class GwInfoManager {

	use Nette\SmartObject;

	/**
	 * @var CommandManager
	 * @inject
	 */
	private $commandManager;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command Manager
	 */
	public function __construct(CommandManager $commandManager) {
		$this->commandManager = $commandManager;
	}

	/**
	 * Get IPv4 and IPv6 addresses of the gateway
	 * @return array IPv4 and IPv6 addresses
	 */
	public function getIpAddresses() {
		$cmd = 'hostname -I';
		return explode(' ', $this->commandManager->send($cmd));
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

}
