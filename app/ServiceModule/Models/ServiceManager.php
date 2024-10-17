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
use App\ServiceModule\Exceptions\NotImplementedException;
use App\ServiceModule\Exceptions\UnsupportedInitSystemException;

/**
 * Tool for managing services
 */
class ServiceManager {

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
	 * @param CommandManager $commandManager Command manager
	 */
	public function __construct(string $initDaemon, CommandManager $commandManager) {
		if (array_key_exists($initDaemon, $this->initDaemons)) {
			$this->initDaemon = new $this->initDaemons[$initDaemon]($commandManager);
		} else {
			$this->initDaemon = new UnknownManager();
		}
	}

	/**
	 * Disables a service
	 * @param string $service Service name
	 * @param bool $stop Stop service after disabling
	 * @throws NonexistentServiceException
	 * @throws NotImplementedException
	 * @throws UnsupportedInitSystemException
	 */
	public function disable(string $service, bool $stop = true): void {
		$this->initDaemon->disable($service, $stop);
	}

	/**
	 * Disables multiple services
	 * @param array<string> $services Service names
	 * @param bool $stop Stop services after disabling
	 * @throws NonexistentServiceException
	 * @throws NotImplementedException
	 * @throws UnsupportedInitSystemException
	 */
	public function disableMultiple(array $services, bool $stop = true): void {
		$this->initDaemon->disableMultiple($services, $stop);
	}

	/**
	 * Enables a service
	 * @param string $service Service name
	 * @param bool $start Start service after enabling
	 * @throws NonexistentServiceException
	 * @throws NotImplementedException
	 * @throws UnsupportedInitSystemException
	 */
	public function enable(string $service, bool $start = true): void {
		$this->initDaemon->enable($service, $start);
	}

	/**
	 * Enables multiple services
	 * @param array<string> $services Service names
	 * @param bool $start Starts services after enabling
	 * @throws NonexistentServiceException
	 * @throws NotImplementedException
	 * @throws UnsupportedInitSystemException
	 */
	public function enableMultiple(array $services, bool $start = true): void {
		$this->initDaemon->enableMultiple($services, $start);
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
	 * Starts a service
	 * @param string $service Service name
	 * @throws NonexistentServiceException
	 * @throws UnsupportedInitSystemException
	 */
	public function start(string $service): void {
		$this->initDaemon->start($service);
	}

	/**
	 * Starts multiple services
	 * @param array<string> $services Service names
	 * @throws NonexistentServiceException
	 * @throws UnsupportedInitSystemException
	 */
	public function startMultiple(array $services): void {
		$this->initDaemon->startMultiple($services);
	}

	/**
	 * Stops a service
	 * @param string $service Service name
	 * @throws NonexistentServiceException
	 * @throws UnsupportedInitSystemException
	 */
	public function stop(string $service): void {
		$this->initDaemon->stop($service);
	}

	/**
	 * Stops multiple services
	 * @param array<string> $services Service names
	 * @throws NonexistentServiceException
	 * @throws UnsupportedInitSystemException
	 */
	public function stopMultiple(array $services): void {
		$this->initDaemon->stopMultiple($services);
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
	 * @throws UnsupportedInitSystemException
	 */
	public function getStatus(string $serviceName): string {
		return $this->initDaemon->getStatus($serviceName);
	}

}
