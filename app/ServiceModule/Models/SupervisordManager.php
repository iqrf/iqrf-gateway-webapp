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

use App\ServiceModule\Entities\ServiceState;
use App\ServiceModule\Exceptions\NotImplementedException;
use Iqrf\CommandExecutor\CommandExecutor;

/**
 * Tool for managing services (supervisord init daemon in a Docker container)
 */
class SupervisordManager implements IServiceManager {

	/**
	 * Constructor
	 * @param CommandExecutor $commandManager Command manager
	 */
	public function __construct(
		private readonly CommandExecutor $commandManager,
	) {
	}

	/**
	 * Disables service(s)
	 * @param string|array<string> $services Service name(s)
	 * @param bool $stop Stop service(s) after disabling
	 * @throws NotImplementedException The method is not implemented
	 */
	public function disable(string|array $services, bool $stop = true): void {
		throw new NotImplementedException();
	}

	/**
	 * Enables service(s)
	 * @param string|array<string> $services Service name(s)
	 * @param bool $start Start service(s) after enabling
	 * @throws NotImplementedException The method is not implemented
	 */
	public function enable(string|array $services, bool $start = true): void {
		throw new NotImplementedException();
	}

	/**
	 * Checks if the service is active
	 * @param string $serviceName Service name
	 * @throws NotImplementedException The method is not implemented
	 */
	public function isActive(string $serviceName): bool {
		throw new NotImplementedException();
	}

	/**
	 * Checks if the service is enabled
	 * @param string $serviceName Service name
	 * @throws NotImplementedException The method is not implemented
	 */
	public function isEnabled(string $serviceName): bool {
		throw new NotImplementedException();
	}

	/**
	 * Starts service(s)
	 * @param string|array<string> $services Service name(s)
	 */
	public function start(string|array $services): void {
		if (!is_array($services)) {
			$services = [$services];
		}
		foreach ($services as $service) {
			$cmd = 'supervisorctl start ' . escapeshellarg($service);
			$this->commandManager->run($cmd, true);
		}
	}

	/**
	 * Stops service(s)
	 * @param string|array<string> $services Service name(s)
	 */
	public function stop(string|array $services): void {
		if (!is_array($services)) {
			$services = [$services];
		}
		foreach ($services as $service) {
			$cmd = 'supervisorctl stop ' . escapeshellarg($service);
			$this->commandManager->run($cmd, true);
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

	/**
	 * Returns state of the service
	 * @param string $serviceName Service name
	 * @param bool $withStatus Include service status?
	 * @return ServiceState Service state
	 */
	public function getState(string $serviceName, bool $withStatus = false): ServiceState {
		return new ServiceState(
			name: $serviceName,
			enabled: null,
			active: null,
			status: $withStatus ? $this->getStatus($serviceName) : null,
		);
	}

}
