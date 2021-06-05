<?php

/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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
	 */
	public function disable(?string $serviceName = null): void {
		throw new NotImplementedException();
	}

	/**
	 * Enables the service
	 * @var string|null $serviceName Service name
	 */
	public function enable(?string $serviceName = null): void {
		throw new NotImplementedException();
	}

	/**
	 * Checks if the service is active
	 * @var string|null $serviceName Service name
	 */
	public function isActive(?string $serviceName = null): bool {
		throw new NotImplementedException();
	}

	/**
	 * Checks if the service is enabled
	 * @var string|null $serviceName Service name
	 */
	public function isEnabled(?string $serviceName = null): bool {
		throw new NotImplementedException();
	}

	/**
	 * Starts the service
	 * @var string|null $serviceName Service name
	 */
	public function start(?string $serviceName = null): void {
		$serviceName = $serviceName ?? $this->serviceName;
		$cmd = 'supervisorctl start ' . $serviceName;
		$this->commandManager->run($cmd, true);
	}

	/**
	 * Stops the service
	 * @var string|null $serviceName Service name
	 */
	public function stop(?string $serviceName = null): void {
		$serviceName = $serviceName ?? $this->serviceName;
		$cmd = 'supervisorctl stop ' . $serviceName;
		$this->commandManager->run($cmd, true);
	}

	/**
	 * Restarts the service
	 * @var string|null $serviceName Service name
	 */
	public function restart(?string $serviceName = null): void {
		$serviceName = $serviceName ?? $this->serviceName;
		$cmd = 'supervisorctl restart ' . $serviceName;
		$this->commandManager->run($cmd, true);
	}

	/**
	 * Returns status of the service
	 * @var string|null $serviceName Service name
	 * @return string Service status
	 */
	public function getStatus(?string $serviceName = null): string {
		$serviceName = $serviceName ?? $this->serviceName;
		$cmd = 'supervisorctl status ' . $serviceName;
		return $this->commandManager->run($cmd, true)->getStdout();
	}

}
