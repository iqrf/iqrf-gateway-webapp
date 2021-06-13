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

namespace App\NetworkModule\Models;

use App\CoreModule\Models\CommandManager;
use App\NetworkModule\Entities\Connection;
use App\NetworkModule\Entities\WifiConnection;
use App\NetworkModule\Entities\WifiNetwork;
use App\NetworkModule\Enums\ConnectionTypes;
use App\NetworkModule\Enums\WifiMode;
use App\NetworkModule\Enums\WifiSecurity;
use App\NetworkModule\Exceptions\NetworkManagerException;

/**
 * WiFI network manager
 */
class WifiManager {

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
	 * Lists available WiFi networks
	 * @return array<WifiNetwork> Available WiFi networks
	 */
	public function list(): array {
		$output = $this->commandManager->run('nmcli -t -f in-use,bssid,ssid,mode,chan,rate,signal,bars,security device wifi list --rescan auto', true);
		if ($output->getExitCode() !== 0) {
			throw new NetworkManagerException($output->getStderr());
		}
		$gateway = file_exists('/etc/iqrf-gateway.json');
		$networks = [];
		$blacklist = [];
		foreach (explode(PHP_EOL, $output->getStdout()) as $network) {
			if ($network === '') {
				continue;
			}
			$deserializedEntry = WifiNetwork::nmCliDeserialize($network);
			$networks[] = $deserializedEntry;
			$blacklist[] = $deserializedEntry->getSsid();
		}
		$networks = array_merge($networks, $this->findHotspots($blacklist));
		if ($gateway) {
			$networks = array_filter($networks, function ($item): bool {
				$security = $item->getSecurity();
				return $security !== WifiSecurity::OPEN() && $security !== WifiSecurity::WEP();
			});
		}
		return $networks;
	}

	/**
	 * Finds hotspot networks
	 * @param array<int, string> $blacklist Wifi network blacklist
	 * @return array<WifiNetwork> Array of hotspot networks
	 */
	private function findHotspots(array $blacklist): array {
		$output = $this->commandManager->run('nmcli -t connection show', true)->getStdout();
		if ($output === '') {
			return [];
		}
		$array = explode(PHP_EOL, trim($output));
		$aps = [];
		foreach ($array as $item) {
			$connection = Connection::nmCliDeserialize($item);
			if ($connection->getType() !== ConnectionTypes::WIFI()) {
				continue;
			}
			$output = $this->commandManager->run('nmcli -t -s connection show ' . $connection->getUuid()->toString(), true)->getStdout();
			if ($output === '') {
				continue;
			}
			$connection = WifiConnection::nmCliDeserialize($output);
			if ($connection->getMode() !== WifiMode::AP()) {
				continue;
			}
			$network = $connection->toWifiNetwork();
			if (in_array($network->getSsid(), $blacklist, true)) {
				continue;
			}
			$aps[] = $network;
		}
		return $aps;
	}

}
