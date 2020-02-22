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

/**
 * Network manager
 */
class NetworkManager {

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 */
	public function __construct(CommandManager $commandManager) {
		$this->commandManager = $commandManager;
	}

	/**
	 * Returns the gateway's hostname
	 * @return string Hostname
	 */
	public function getHostname(): string {
		$cmd = 'hostname -f';
		return $this->commandManager->run($cmd)->getStdout();
	}

	/**
	 * Returns information about network interfaces
	 * @return mixed[] Network interfaces
	 */
	public function getInterfaces(): array {
		$interfaces = [];
		$ipAddresses = $this->getIpAddresses();
		$macAddresses = $this->getMacAddresses();
		foreach ($this->listsInterfaces() as $interface) {
			$interfaces[] = [
				'name' => $interface,
				'macAddress' => $macAddresses[$interface] ?? null,
				'ipAddresses' => $ipAddresses[$interface] ?? null,
			];
		}
		return $interfaces;
	}

	/**
	 * Returns IPv4 and IPv6 addresses of the gateway
	 * @return string[][] IPv4 and IPv6 addresses
	 */
	public function getIpAddresses(): array {
		$addresses = [];
		foreach ($this->listsInterfaces() as $interface) {
			$cmd = 'ip a s ' . $interface . ' | grep inet | grep global | grep -v temporary | grep -v mngtmpaddr | awk \'{print $2}\'';
			$output = $this->commandManager->run($cmd, true)->getStdout();
			if ($output !== '') {
				$addresses[$interface] = explode(PHP_EOL, $output);
			}
		}
		return $addresses;
	}

	/**
	 * Lists network interfaces
	 * @return string[] Network interfaces
	 */
	private function listsInterfaces(): array {
		$interfaces = $this->commandManager->run('ls /sys/class/net | awk \'{ print $0 }\'', true)->getStdout();
		return array_diff(explode(PHP_EOL, $interfaces), ['lo']);
	}

	/**
	 * Returns MAC addresses of the gateway
	 * @return string[] MAC addresses array
	 */
	public function getMacAddresses(): array {
		$addresses = [];
		foreach ($this->listsInterfaces() as $interface) {
			$cmd = 'cat /sys/class/net/' . $interface . '/address';
			$addresses[$interface] = $this->commandManager->run($cmd, true)->getStdout();
		}
		return $addresses;
	}

}
