<?php

/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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

namespace App\ConfigModule\Models;

use App\CoreModule\Models\CommandManager;

/**
 * IQRF CDC/SPI/UART interface manager
 */
class IqrfManager {

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 */
	public function __construct(
		private readonly CommandManager $commandManager,
	) {
	}

	/**
	 * Creates a list of USB CDC interfaces available in the system
	 * @return array<string> USB CDC interfaces available in the system
	 */
	public function getCdcInterfaces(): array {
		$command = 'ls -1 /dev/ttyACM*';
		return $this->getInterfaces($command);
	}

	/**
	 * Creates a list of interfaces available in the system
	 * @param string $command Command to list interfaces
	 * @return array<string> List of interfaces available in the system
	 */
	private function getInterfaces(string $command): array {
		$interfaces = [];
		$ls = $this->commandManager->run($command, true)->getStdout();
		foreach (explode(PHP_EOL, $ls) as $interface) {
			if ($interface !== '') {
				$interfaces[] = $interface;
			}
		}
		return $interfaces;
	}

	/**
	 * Creates a list of SPI interfaces available in the system
	 * @return array<string> SPI interfaces available in the system
	 */
	public function getSpiInterfaces(): array {
		$command = 'ls -1 /dev/spidev*';
		return $this->getInterfaces($command);
	}

	/**
	 * Creates a list of UART interfaces available in the system
	 * @return array<string> UART interfaces available in the system
	 */
	public function getUartInterfaces(): array {
		$command = 'ls -1 /dev/ttyAMA* /dev/ttyS*';
		return $this->getInterfaces($command);
	}

}
