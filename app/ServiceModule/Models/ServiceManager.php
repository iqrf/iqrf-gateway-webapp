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
use App\ServiceModule\Exceptions\NonexistentServiceException;
use App\ServiceModule\Exceptions\NotImplementedException;
use App\ServiceModule\Exceptions\UnsupportedInitSystemException;
use Iqrf\CommandExecutor\CommandExecutor;

/**
 * Tool for managing services
 */
class ServiceManager implements IServiceManager {

	/**
	 * @var IServiceManager Init daemon service manager
	 */
	private IServiceManager $initDaemon;

	/**
	 * @var array<string> Init daemon service managers
	 */
	private array $initDaemons = [
		'docker-supervisor' => SupervisordManager::class,
		'systemd' => SystemDManager::class,
	];

	/**
	 * Constructor
	 * @param string $initDaemon Init daemon
	 * @param CommandExecutor $commandManager Command manager
	 */
	public function __construct(string $initDaemon, CommandExecutor $commandManager) {
		if (array_key_exists($initDaemon, $this->initDaemons)) {
			$this->initDaemon = new $this->initDaemons[$initDaemon]($commandManager);
		} else {
			$this->initDaemon = new UnknownManager();
		}
	}

	/**
	 * Disables service(s)
	 * @param string|array<string> $services Service name(s)
	 * @param bool $stop Stop service(s) after disabling
	 * @throws NonexistentServiceException
	 * @throws NotImplementedException
	 * @throws UnsupportedInitSystemException
	 */
	public function disable(string|array $services, bool $stop = true): void {
		$this->initDaemon->disable($services, $stop);
	}

	/**
	 * Enables service(s)
	 * @param string|array<string> $services Service name(s)
	 * @param bool $start Start service(s) after enabling
	 * @throws NonexistentServiceException
	 * @throws NotImplementedException
	 * @throws UnsupportedInitSystemException
	 */
	public function enable(string|array $services, bool $start = true): void {
		$this->initDaemon->enable($services, $start);
	}

	/**
	 * Checks if the service is active
	 * @param string $serviceName Service name
	 * @throws NonexistentServiceException
	 * @throws NotImplementedException
	 * @throws UnsupportedInitSystemException
	 */
	public function isActive(string $serviceName): bool {
		return $this->initDaemon->isActive($serviceName);
	}

	/**
	 * Checks if the service is enabled
	 * @param string $serviceName Service name
	 * @throws NonexistentServiceException
	 * @throws NotImplementedException
	 * @throws UnsupportedInitSystemException
	 */
	public function isEnabled(string $serviceName): bool {
		return $this->initDaemon->isEnabled($serviceName);
	}

	/**
	 * Starts service(s)
	 * @param string|array<string> $services Service names(s)
	 * @throws NonexistentServiceException
	 * @throws UnsupportedInitSystemException
	 */
	public function start(string|array $services): void {
		$this->initDaemon->start($services);
	}

	/**
	 * Stops services(s)
	 * @param string|array<string> $services Service names(s)
	 * @throws NonexistentServiceException
	 * @throws UnsupportedInitSystemException
	 */
	public function stop(string|array $services): void {
		$this->initDaemon->stop($services);
	}

	/**
	 * Restarts the service
	 * @param string $serviceName Service name
	 * @throws NonexistentServiceException
	 * @throws UnsupportedInitSystemException
	 */
	public function restart(string $serviceName): void {
		$this->initDaemon->restart($serviceName);
	}

	/**
	 * Returns status of the service
	 * @param string $serviceName Service name
	 * @return string Service status
	 * @throws NonexistentServiceException
	 * @throws UnsupportedInitSystemException
	 */
	public function getStatus(string $serviceName): string {
		return $this->initDaemon->getStatus($serviceName);
	}

	/**
	 * Returns state of the service
	 * @param string $serviceName Service name
	 * @param bool $withStatus Include service status?
	 * @return ServiceState Service state
	 * @throws NonexistentServiceException
	 * @throws UnsupportedInitSystemException
	 */
	public function getState(string $serviceName, bool $withStatus = false): ServiceState {
		return $this->initDaemon->getState($serviceName, $withStatus);
	}

}
