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

namespace App\ServiceModule\Models;

use App\CoreModule\Models\CommandManager;
use App\ServiceModule\Entities\ServiceState;
use App\ServiceModule\Exceptions\NonexistentServiceException;

/**
 * Tool for managing services (systemD init daemon)
 */
class SystemDManager implements IServiceManager {

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
	 * @throws NonexistentServiceException
	 */
	public function disable(string $serviceName): void {
		$cmd = 'systemctl disable ' . $this->formatServiceName($serviceName);
		$command = $this->commandManager->run($cmd, true);
		if ($command->getExitCode() !== 0) {
			throw new NonexistentServiceException($command->getStderr());
		}
		$this->stop($serviceName);
	}

	/**
	 * Enables the service
	 * @param string $serviceName Service name
	 * @throws NonexistentServiceException
	 */
	public function enable(string $serviceName): void {
		$cmd = 'systemctl enable ' . $this->formatServiceName($serviceName);
		$command = $this->commandManager->run($cmd, true);
		if ($command->getExitCode() !== 0) {
			throw new NonexistentServiceException($command->getStderr());
		}
		$this->start($serviceName);
	}

	/**
	 * Checks if the service is active
	 * @param string $serviceName Service name
	 * @return bool Is service active?
	 * @throws NonexistentServiceException
	 */
	public function isActive(string $serviceName): bool {
		$cmd = 'systemctl is-active ' . $this->formatServiceName($serviceName);
		$command = $this->commandManager->run($cmd, true);
		if ($command->getExitCode() === 4) {
			throw new NonexistentServiceException($command->getStderr());
		}
		return $command->getStdout() === 'active';
	}

	/**
	 * Checks if the service is enabled
	 * @param string $serviceName Service name
	 * @return bool Is service enabled?
	 * @throws NonexistentServiceException
	 */
	public function isEnabled(string $serviceName): bool {
		$cmd = 'systemctl is-enabled ' . $this->formatServiceName($serviceName);
		$command = $this->commandManager->run($cmd, true);
		if ($command->getExitCode() === 1 &&
			str_contains($command->getStderr(), 'No such file or directory')) {
			throw new NonexistentServiceException($command->getStderr());
		}
		return $command->getStdout() === 'enabled';
	}

	/**
	 * Starts the service
	 * @param string $serviceName Service name
	 * @throws NonexistentServiceException
	 */
	public function start(string $serviceName): void {
		$cmd = 'systemctl start ' . $this->formatServiceName($serviceName);
		$command = $this->commandManager->run($cmd, true);
		if ($command->getExitCode() !== 0) {
			throw new NonexistentServiceException($command->getStderr());
		}
	}

	/**
	 * Stops the service
	 * @param string $serviceName Service name
	 * @throws NonexistentServiceException
	 */
	public function stop(string $serviceName): void {
		$cmd = 'systemctl stop ' . $this->formatServiceName($serviceName);
		$command = $this->commandManager->run($cmd, true);
		if ($command->getExitCode() !== 0) {
			throw new NonexistentServiceException($command->getStderr());
		}
	}

	/**
	 * Restarts the service
	 * @param string $serviceName Service name
	 * @throws NonexistentServiceException
	 */
	public function restart(string $serviceName): void {
		$cmd = 'systemctl restart ' . $this->formatServiceName($serviceName);
		$command = $this->commandManager->run($cmd, true);
		if ($command->getExitCode() !== 0) {
			throw new NonexistentServiceException($command->getStderr());
		}
	}

	/**
	 * Returns status of the service
	 * @param string $serviceName Service name
	 * @return string Service status
	 * @throws NonexistentServiceException
	 */
	public function getStatus(string $serviceName): string {
		$cmd = 'systemctl status ' . $this->formatServiceName($serviceName);
		$command = $this->commandManager->run($cmd, true);
		if ($command->getExitCode() === 4) {
			throw new NonexistentServiceException($command->getStderr());
		}
		return $command->getStdout();
	}

	/**
	 * Returns state of the service
	 * @param string $serviceName Service name
	 * @param bool $withStatus Include service status?
	 * @return ServiceState Service state
	 * @throws NonexistentServiceException
	 */
	public function getState(string $serviceName, bool $withStatus = false): ServiceState {
		$cmd = 'systemctl show --property=UnitFileState,ActiveState ' . $this->formatServiceName($serviceName);
		$command = $this->commandManager->run($cmd, true);
		if ($command->getExitCode() !== 0) {
			throw new NonexistentServiceException($command->getStderr());
		}
		$parsedOutput = parse_ini_string($command->getStdout());
		$status = null;
		if ($withStatus) {
			$status = $this->getStatus($serviceName);
		}
		return new ServiceState(
			name: $serviceName,
			enabled: $parsedOutput['UnitFileState'] === 'enabled',
			active: $parsedOutput['ActiveState'] === 'active',
			status: $status,
		);
	}

	/**
	 * Formats the service name
	 * @param string $serviceName Service name to format
	 * @return string Formatted service name
	 */
	private function formatServiceName(string $serviceName): string {
		return escapeshellarg($serviceName . '.service');
	}

}
