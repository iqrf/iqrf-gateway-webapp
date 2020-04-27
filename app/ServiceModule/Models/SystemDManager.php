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

namespace App\ServiceModule\Models;

use App\CoreModule\Models\CommandManager;
use App\ServiceModule\Exceptions\NonexistentServiceException;
use Nette\SmartObject;

/**
 * Tool for managing services (systemD init daemon)
 */
class SystemDManager implements IServiceManager {

	use SmartObject;

	/**
	 * @var CommandManager Command Manager
	 */
	private $commandManager;

	/**
	 * @var string Name of service
	 */
	private $serviceName = 'iqrf-gateway-daemon';

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 * @param string|null $serviceName Service name
	 */
	public function __construct(CommandManager $commandManager, ?string $serviceName = null) {
		$this->commandManager = $commandManager;
		if ($serviceName !== null) {
			$this->serviceName = $serviceName;
		}
	}

	/**
	 * Disables the service
	 * @var string|null $serviceName Service name
	 * @throws NonexistentServiceException
	 */
	public function disable(?string $serviceName = null): void {
		$serviceName = $serviceName ?? $this->serviceName;
		$cmd = 'systemctl disable ' . $serviceName . '.service';
		$command = $this->commandManager->run($cmd, true);
		if ($command->getExitCode() !== 0) {
			throw new NonexistentServiceException($command->getStderr());
		}
		$this->stop($serviceName);
	}

	/**
	 * Enables the service
	 * @var string|null $serviceName Service name
	 * @throws NonexistentServiceException
	 */
	public function enable(?string $serviceName = null): void {
		$serviceName = $serviceName ?? $this->serviceName;
		$cmd = 'systemctl enable ' . $serviceName . '.service';
		$command = $this->commandManager->run($cmd, true);
		if ($command->getExitCode() !== 0) {
			throw new NonexistentServiceException($command->getStderr());
		}
		$this->start($serviceName);
	}

	/**
	 * Checks if the service is active
	 * @var string|null $serviceName Service name
	 * @return bool Is service active?
	 */
	public function isActive(?string $serviceName = null): bool {
		$serviceName = $serviceName ?? $this->serviceName;
		$cmd = 'systemctl is-active ' . $serviceName . '.service';
		$command = $this->commandManager->run($cmd, true);
		return $command->getStdout() === 'active';
	}

	/**
	 * Checks if the service is enabled
	 * @var string|null $serviceName Service name
	 * @return bool Is service enabled?
	 * @throws NonexistentServiceException
	 */
	public function isEnabled(?string $serviceName = null): bool {
		$serviceName = $serviceName ?? $this->serviceName;
		$cmd = 'systemctl is-enabled ' . $serviceName . '.service';
		$command = $this->commandManager->run($cmd, true);
		return $command->getStdout() === 'enabled';
	}

	/**
	 * Starts the service
	 * @var string|null $serviceName Service name
	 * @throws NonexistentServiceException
	 */
	public function start(?string $serviceName = null): void {
		$serviceName = $serviceName ?? $this->serviceName;
		$cmd = 'systemctl start ' . $serviceName . '.service';
		$command = $this->commandManager->run($cmd, true);
		if ($command->getExitCode() !== 0) {
			throw new NonexistentServiceException($command->getStderr());
		}
	}

	/**
	 * Stops the service
	 * @var string|null $serviceName Service name
	 * @throws NonexistentServiceException
	 */
	public function stop(?string $serviceName = null): void {
		$serviceName = $serviceName ?? $this->serviceName;
		$cmd = 'systemctl stop ' . $serviceName . '.service';
		$command = $this->commandManager->run($cmd, true);
		if ($command->getExitCode() !== 0) {
			throw new NonexistentServiceException($command->getStderr());
		}
	}

	/**
	 * Restarts the service
	 * @var string|null $serviceName Service name
	 * @throws NonexistentServiceException
	 */
	public function restart(?string $serviceName = null): void {
		$serviceName = $serviceName ?? $this->serviceName;
		$cmd = 'systemctl restart ' . $serviceName . '.service';
		$command = $this->commandManager->run($cmd, true);
		if ($command->getExitCode() !== 0) {
			throw new NonexistentServiceException($command->getStderr());
		}
	}

	/**
	 * Returns status of the service
	 * @var string|null $serviceName Service name
	 * @return string Service status
	 * @throws NonexistentServiceException
	 */
	public function getStatus(?string $serviceName = null): string {
		$serviceName = $serviceName ?? $this->serviceName;
		$cmd = 'systemctl status ' . $serviceName . '.service';
		$command = $this->commandManager->run($cmd, true);
		return $command->getStdout();
	}

}
