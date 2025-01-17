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

namespace App\NetworkModule\Models;

use App\NetworkModule\Enums\ConnectivityState;
use App\NetworkModule\Exceptions\NetworkManagerException;
use Iqrf\CommandExecutor\CommandExecutor;

/**
 * Network connectivity manager
 */
class ConnectivityManager {

	/**
	 * Constructor
	 * @param CommandExecutor $commandManager Command manager
	 */
	public function __construct(
		private readonly CommandExecutor $commandManager,
	) {
	}

	/**
	 * Checks network connectivity state
	 * @return ConnectivityState Network connectivity state
	 */
	public function check(): ConnectivityState {
		$output = $this->commandManager->run('nmcli -t networking connectivity check', true);
		if ($output->getExitCode() !== 0) {
			throw new NetworkManagerException($output->getStderr());
		}
		return ConnectivityState::from($output->getStdout());
	}

}
