<?php

/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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
 * Tool for powering off and rebooting IQRF Gateway
 */
class PowerManager {

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
	 * Powers off IQRF Gateway
	 * @return array{timestamp: int} Shutdown timestamp
	 */
	public function powerOff(): array {
		$this->commandManager->run('shutdown -P `date --date "now + 60 seconds" "+%H:%M"`', true);
		return $this->calculateNextMinute();
	}

	/**
	 * Reboots IQRF Gateway
	 * @return array{timestamp: int} Restart timestamp
	 */
	public function reboot(): array {
		$this->commandManager->run('shutdown -r `date --date "now + 60 seconds" "+%H:%M"`', true);
		return $this->calculateNextMinute();
	}

	/**
	 * Calculates timestamp for the next whole minute
	 * @return array{timestamp: int} Timestamp
	 */
	private function calculateNextMinute(): array {
		$timestamp = (int) (ceil(time() / 60) * 60);
		return ['timestamp' => $timestamp];
	}

}
