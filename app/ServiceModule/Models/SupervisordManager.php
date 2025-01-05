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

namespace App\ServiceModule\Models;

use App\CoreModule\Models\CommandManager;
use App\ServiceModule\Exceptions\NotImplementedException;

/**
 * Tool for managing services (supervisord init daemon in a Docker container)
 */
class SupervisordManager implements IServiceManager {

	/**
	 * @var CommandManager Command Manager
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
	 * Disables multiple services
	 * @param string $service Service names
	 * @param bool $stop Stop service after disabling
	 */
	public function disable(string $service, bool $stop = true): void {
		throw new NotImplementedException();
	}

	/**
	 * Disables multiple services
	 * @param array<string> $services Service names
	 * @param bool $stop Stop services after disabling
	 */
	public function disableMultiple(array $services, bool $stop = true): void {
		throw new NotImplementedException();
	}

	/**
	 * Enables a service
	 * @param string $service Service name
	 * @param bool $start Start service after enable
	 */
	public function enable(string $service, bool $start = true): void {
		throw new NotImplementedException();
	}

	/**
	 * Enables multiple services
	 * @param array<string> $services Service names
	 * @param bool $start Start services after enabling
	 */
	public function enableMultiple(array $services, bool $start = true): void {
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
	 * Starts a service
	 * @param string $service Service name
	 */
	public function start(string $service): void {
		$cmd = 'supervisorctl start ' . escapeshellarg($service);
		$this->commandManager->run($cmd, true);
	}

	/**
	 * Starts multiple services
	 * @param array<string> $services Service names
	 */
	public function startMultiple(array $services): void {
		foreach ($services as $service) {
			$this->start($service);
		}
	}

	/**
	 * Stops a service
	 * @param string $service Service name
	 */
	public function stop(string $service): void {
		$cmd = 'supervisorctl stop ' . escapeshellarg($service);
		$this->commandManager->run($cmd, true);
	}

	/**
	 * Stops multiple services
	 * @param array<string> $services Service names
	 */
	public function stopMultiple(array $services): void {
		foreach ($services as $service) {
			$this->stop($service);
		}
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
