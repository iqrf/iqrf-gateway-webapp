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
use App\NetworkModule\Entities\InterfaceStatus;

/**
 * Network interface manager
 */
class InterfaceManager {

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
	 * Connects the network interface
	 * @param string $name Network interface name
	 */
	public function connect(string $name): void {
		$this->commandManager->run('nmcli -t device connect ' . $name, true);
	}

	/**
	 * Discoonnects the network interface
	 * @param string $name Network interface name
	 */
	public function disconnect(string $name): void {
		$this->commandManager->run('nmcli -t device disconnect ' . $name, true);
	}

	/**
	 * Lists network interfaces
	 * @return array<InterfaceStatus> Network interfaces
	 */
	public function list(): array {
		$output = $this->commandManager->run('nmcli -t device status', true)->getStdout();
		$array = explode(PHP_EOL, trim($output));
		foreach ($array as &$row) {
			$row = InterfaceStatus::fromString($row);
		}
		return $array;
	}

}
