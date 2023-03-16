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

namespace App\ServiceModule\Models;

use App\CoreModule\Models\CommandManager;
use App\ServiceModule\Exceptions\NotImplementedException;

/**
 * Tool for managing services (supervisord init daemon in a Docker container)
 */
class SupervisordManager implements IServiceManager {

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 */
	public function __construct(
		private readonly CommandManager $commandManager,
	) {
	}

	/**
	 * Disables the service
	 * @param string $serviceName Service name
	 */
	public function disable(string $serviceName): void {
		throw new NotImplementedException();
	}

	/**
	 * Enables the service
	 * @param string $serviceName Service name
	 */
	public function enable(string $serviceName): void {
		throw new NotImplementedException();
	}

	/**
	 * Checks if the service is active
	 * @param string $serviceName Service name
	 */
	public function isActive(string $serviceName): bool {
		throw new NotImplementedException();
	}

	/**
	 * Checks if the service is enabled
	 * @param string $serviceName Service name
	 */
	public function isEnabled(string $serviceName): bool {
		throw new NotImplementedException();
	}

	/**
	 * Starts the service
	 * @param string $serviceName Service name
	 */
	public function start(string $serviceName): void {
		$cmd = 'supervisorctl start ' . escapeshellarg($serviceName);
		$this->commandManager->run($cmd, true);
	}

	/**
	 * Stops the service
	 * @param string $serviceName Service name
	 */
	public function stop(string $serviceName): void {
		$cmd = 'supervisorctl stop ' . escapeshellarg($serviceName);
		$this->commandManager->run($cmd, true);
	}

	/**
	 * Restarts the service
	 * @param string $serviceName Service name
	 */
	public function restart(string $serviceName): void {
		$cmd = 'supervisorctl restart ' . escapeshellarg($serviceName);
		$this->commandManager->run($cmd, true);
	}

	/**
	 * Returns status of the service
	 * @param string $serviceName Service name
	 * @return string Service status
	 */
	public function getStatus(string $serviceName): string {
		$cmd = 'supervisorctl status ' . escapeshellarg($serviceName);
		return $this->commandManager->run($cmd, true)->getStdout();
	}

}
