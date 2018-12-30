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

namespace App\ConfigModule\Models;

use App\CoreModule\Models\CommandManager;
use Nette\SmartObject;

/**
 * IQRF CDC/SPI/UART interface manager
 */
class IqrfManager {

	use SmartObject;

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
	 * Create list of USB CDC interfaces available in the system
	 * @return string[] USB CDC interfaces available in the system
	 */
	public function getCdcInterfaces(): array {
		$command = "ls /dev/ttyACM* | awk '{ print $0 }'";
		return $this->getInterfaces($command);
	}

	/**
	 * Create list of interfaces available in the system
	 * @param string $command Command to list interfaces
	 * @return string[] List of interfaces available in the system
	 */
	private function getInterfaces(string $command): array {
		$interfaces = [];
		$ls = $this->commandManager->send($command, true);
		foreach (explode(PHP_EOL, $ls) as $interface) {
			if ($interface !== '') {
				array_push($interfaces, $interface);
			}
		}
		return $interfaces;
	}

	/**
	 * Create list of SPI interfaces available in the system
	 * @return string[] SPI interfaces available in the system
	 */
	public function getSpiInterfaces(): array {
		$command = "ls /dev/spidev* | awk '{ print $0 }'";
		return $this->getInterfaces($command);
	}

	/**
	 * Create list of UART interfaces available in the system
	 * @return string[] UART interfaces available in the system
	 */
	public function getUartInterfaces(): array {
		$command = "ls /dev/ttyAMA* /dev/ttyS* | awk '{ print $0 }'";
		return $this->getInterfaces($command);
	}

}
