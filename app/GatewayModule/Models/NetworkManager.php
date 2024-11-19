<?php

/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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

use App\NetworkModule\Entities\MultiAddress;
use Iqrf\CommandExecutor\CommandExecutor;

/**
 * Network manager
 */
class NetworkManager {

	/**
	 * Constructor
	 * @param CommandExecutor $commandManager Command manager
	 */
	public function __construct(
		private readonly CommandExecutor $commandManager,
	) {
	}

	/**
	 * Returns the gateway's hostname
	 * @return string Hostname
	 */
	public function getHostname(): string {
		$command = $this->commandManager->run('hostname -f');
		if ($command->getExitCode() === 0) {
			return $command->getStdout();
		}
		$hostname = gethostname();
		if ($hostname !== false) {
			return $hostname;
		}
		return '';
	}

	/**
	 * Returns information about network interfaces
	 * @return array<array{name: string, macAddress: string|null, ipAddresses: array<string>|null}> Network interfaces
	 */
	public function getInterfaces(): array {
		$array = [];
		$interfaces = $this->listsInterfaces();
		$ipAddresses = $this->getIpAddresses($interfaces);
		$macAddresses = $this->getMacAddresses($interfaces);
		foreach ($interfaces as $interface) {
			$array[] = [
				'name' => $interface,
				'macAddress' => $macAddresses[$interface] ?? null,
				'ipAddresses' => $ipAddresses[$interface] ?? null,
			];
		}
		return $array;
	}

	/**
	 * Returns IPv4 and IPv6 addresses of the gateway
	 * @param array<string> $interfaces Network interfaces
	 * @return array<array<string>> IPv4 and IPv6 addresses
	 */
	public function getIpAddresses(array $interfaces): array {
		$addresses = [];
		foreach ($interfaces as $interface) {
			$cmd = 'ip a s ' . escapeshellarg($interface) . ' | grep inet | grep global | grep -v temporary | awk \'{print $2}\' | grep \'/\'';
			$output = $this->commandManager->run($cmd, true)->getStdout();
			if ($output !== '') {
				$addresses[$interface] = array_map(static fn (string $address): string => MultiAddress::fromPrefix($address)->getAddress()->getProtocolAppropriateAddress(), explode(PHP_EOL, $output));
			}
		}
		return $addresses;
	}

	/**
	 * Returns MAC addresses of the gateway
	 * @param array<string> $interfaces Network interfaces
	 * @return array<string|null> MAC addresses array
	 */
	protected function getMacAddresses(array $interfaces): array {
		$addresses = [];
		foreach ($interfaces as $interface) {
			$cmd = 'cat /sys/class/net/' . $interface . '/address';
			$output = $this->commandManager->run($cmd, true)->getStdout();
			$addresses[$interface] = $output === '' ? null : $output;
		}
		return $addresses;
	}

	/**
	 * Lists network interfaces
	 * @return array<string> Network interfaces
	 */
	private function listsInterfaces(): array {
		$interfaces = $this->commandManager->run('ls /sys/class/net | awk \'{ print $0 }\'', true)->getStdout();
		return array_diff(explode(PHP_EOL, $interfaces), ['lo']);
	}

}
