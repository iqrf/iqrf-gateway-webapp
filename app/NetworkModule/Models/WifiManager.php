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

namespace App\NetworkModule\Models;

use App\CoreModule\Models\CommandManager;
use App\NetworkModule\Entities\WifiNetwork;
use App\NetworkModule\Exceptions\NetworkManagerException;

/**
 * WiFi network manager
 */
class WifiManager {

	/**
	 * @var CommandManager Command manager
	 */
	private CommandManager $commandManager;

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
		$fields = ['IN-USE', 'BSSID', 'SSID', 'MODE', 'CHAN', 'RATE', 'SIGNAL', 'SECURITY'];
		$command = sprintf('nmcli -t -f %s device wifi list --rescan auto', implode(',', $fields));
		$output = $this->commandManager->run($command, true);
		if ($output->getExitCode() !== 0) {
			throw new NetworkManagerException($output->getStderr());
		}
		$networks = [];
		foreach (explode(PHP_EOL, $output->getStdout()) as $network) {
			if ($network === '') {
				continue;
			}
			$networks[] = WifiNetwork::nmCliDeserialize($network);
		}
		return $networks;
	}

}
