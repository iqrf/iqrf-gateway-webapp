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

namespace App\NetworkModule\Models;

use App\CoreModule\Models\CommandManager;
use App\NetworkModule\Entities\InterfaceStatus;
use App\NetworkModule\Enums\InterfaceTypes;
use App\NetworkModule\Exceptions\NetworkManagerException;
use App\NetworkModule\Exceptions\NonexistentDeviceException;

/**
 * Network interface manager
 */
class InterfaceManager {

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 */
	public function __construct(
		private readonly CommandManager $commandManager,
	) {
	}

	/**
	 * Connects the network interface
	 * @param string $name Network interface name
	 */
	public function connect(string $name): void {
		$output = $this->commandManager->run('nmcli -t device connect ' . escapeshellarg($name), true);
		$exitCode = $output->getExitCode();
		if ($exitCode !== 0) {
			$this->handleError($exitCode, $output->getStderr());
		}
	}

	/**
	 * Disconnects the network interface
	 * @param string $name Network interface name
	 */
	public function disconnect(string $name): void {
		$output = $this->commandManager->run('nmcli -t device disconnect ' . escapeshellarg($name), true);
		$exitCode = $output->getExitCode();
		if ($exitCode !== 0) {
			$this->handleError($exitCode, $output->getStderr());
		}
	}

	/**
	 * Lists network interfaces
	 * @param InterfaceTypes|null $type Network interface type
	 * @return array<InterfaceStatus> Network interfaces
	 */
	public function list(?InterfaceTypes $type = null): array {
		$output = $this->commandManager->run('nmcli -t -f DEVICE,TYPE device status', true)->getStdout();
		$array = explode(PHP_EOL, trim($output));
		$interfaces = [];
		foreach ($array as $row) {
			$parts = preg_split('~(?<!\\\)' . preg_quote(':', '~') . '~', $row);
			if ($type !== null && $type !== InterfaceTypes::from($parts[1])) {
				continue;
			}
			$interfaceOutput = $this->commandManager->run('nmcli -t -f GENERAL,CONNECTIONS.AVAILABLE-CONNECTIONS device show ' . $parts[0], true)->getStdout();
			$interfaces[] = InterfaceStatus::nmCliDeserialize($interfaceOutput);
		}
		return $interfaces;
	}

	/**
	 * Error handler function for NMCLI
	 * @param int $code NMCLI exit code
	 * @param string $error NMCLI stderr
	 * @throws NetworkManagerException
	 * @throws NonexistentDeviceException
	 */
	private function handleError(int $code, string $error): void {
		if ($code === 10) {
			throw new NonexistentDeviceException($error);
		}
		throw new NetworkManagerException($error);
	}

}
