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
use App\ServiceModule\Exceptions\NonexistentServiceException;
use Nette\Utils\Strings;

/**
 * Tool for managing services (systemD init daemon)
 */
class SystemDManager implements IServiceManager {

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
	 * Disables the service(s)
	 * @param string|array<string> $service Service name(s)
	 * @param bool $stop Stop service after disabling
	 * @throws NonexistentServiceException
	 */
	public function disable(mixed $service, bool $stop = true): void {
		if (is_array($service)) {
			$arg = $this->formatServiceNames($service);
		} else {
			$arg = $this->formatServiceName($service);
		}
		$cmd = sprintf('systemctl disable%s %s', $stop ? ' --now' : '', $arg);
		$command = $this->commandManager->run($cmd, true);
		if ($command->getExitCode() !== 0) {
			throw new NonexistentServiceException($command->getStderr());
		}
	}

	/**
	 * Enables the service(s)
	 * @param string|array<string> $service Service name(s)
	 * @param bool $start Start service after enable
	 * @throws NonexistentServiceException
	 */
	public function enable(mixed $service, bool $start = true): void {
		if (is_array($service)) {
			$arg = $this->formatServiceNames($service);
		} else {
			$arg = $this->formatServiceName($service);
		}
		$cmd = sprintf('systemctl enable%s %s', $start ? ' --now' : '', $arg);
		$command = $this->commandManager->run($cmd, true);
		if ($command->getExitCode() !== 0) {
			throw new NonexistentServiceException($command->getStderr());
		}
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
			Strings::contains($command->getStderr(), 'No such file or directory')) {
			throw new NonexistentServiceException($command->getStderr());
		}
		return $command->getStdout() === 'enabled';
	}

	/**
	 * Starts the service(s)
	 * @param string|array<string> $service Service name(s)
	 * @throws NonexistentServiceException
	 */
	public function start(mixed $service): void {
		if (is_array($service)) {
			$arg = $this->formatServiceNames($service);
		} else {
			$arg = $this->formatServiceName($service);
		}
		$cmd = 'systemctl start ' . $arg;
		$command = $this->commandManager->run($cmd, true);
		if ($command->getExitCode() !== 0) {
			throw new NonexistentServiceException($command->getStderr());
		}
	}

	/**
	 * Stops the service(s)
	 * @param string|array<string> $service Service name(s)
	 * @throws NonexistentServiceException
	 */
	public function stop(mixed $service): void {
		if (is_array($service)) {
			$arg = $this->formatServiceNames($service);
		} else {
			$arg = $this->formatServiceName($service);
		}
		$cmd = 'systemctl stop ' . $arg;
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
	 * Formats names of services
	 * @param array<string> $services Service names
	 * @return string Formatted service names
	 */
	private function formatServiceNames(array $services): string {
		return implode(' ', array_map(fn (string $service): string => $this->formatServiceName($service), $services));
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
