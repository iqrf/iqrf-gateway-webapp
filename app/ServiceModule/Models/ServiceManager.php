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
use App\ServiceModule\Exceptions\NotImplementedException;
use App\ServiceModule\Exceptions\UnsupportedInitSystemException;

/**
 * Tool for managing services
 */
class ServiceManager {

	/**
	 * @var IServiceManager Init daemon service manager
	 */
	private $initDaemon;

	/**
	 * @var array<string> Init daemon service managers
	 */
	private $initDaemons = [
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
	 * Disables the service
	 * @var string|null $serviceName Service name
	 * @throws NotImplementedException
	 * @throws UnsupportedInitSystemException
	 */
	public function disable(?string $serviceName = null): void {
		$this->initDaemon->disable($serviceName);
	}

	/**
	 * Enables the service
	 * @var string|null $serviceName Service name
	 * @throws NotImplementedException
	 * @throws UnsupportedInitSystemException
	 */
	public function enable(?string $serviceName = null): void {
		$this->initDaemon->enable($serviceName);
	}

	/**
	 * Checks if the service is active
	 * @var string|null $serviceName Service name
	 * @throws NotImplementedException
	 * @throws UnsupportedInitSystemException
	 */
	public function isActive(?string $serviceName = null): bool {
		return $this->initDaemon->isActive($serviceName);
	}

	/**
	 * Checks if the service is enabled
	 * @var string|null $serviceName Service name
	 * @throws NotImplementedException
	 * @throws UnsupportedInitSystemException
	 */
	public function isEnabled(?string $serviceName = null): bool {
		return $this->initDaemon->isEnabled($serviceName);
	}

	/**
	 * Starts the service
	 * @var string|null $serviceName Service name
	 * @throws UnsupportedInitSystemException
	 */
	public function start(?string $serviceName = null): void {
		$this->initDaemon->start($serviceName);
	}

	/**
	 * Stops the service
	 * @var string|null $serviceName Service name
	 * @throws UnsupportedInitSystemException
	 */
	public function stop(?string $serviceName = null): void {
		$this->initDaemon->stop($serviceName);
	}

	/**
	 * Restarts the service
	 * @var string|null $serviceName Service name
	 * @throws UnsupportedInitSystemException
	 */
	public function restart(?string $serviceName = null): void {
		$this->initDaemon->restart($serviceName);
	}

	/**
	 * Returns status of the service
	 * @var string|null $serviceName Service name
	 * @return string Service status
	 * @throws UnsupportedInitSystemException
	 */
	public function getStatus(?string $serviceName = null): string {
		return $this->initDaemon->getStatus($serviceName);
	}

}
