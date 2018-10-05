<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2018 IQRF Tech s.r.o.
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

namespace App\ServiceModule\Model;

use App\CoreModule\Model\CommandManager;
use App\ServiceModule\Exception\NotSupportedInitSystemException;
use Nette\SmartObject;

/**
 * Tool for managing services
 */
class ServiceManager {

	use SmartObject;

	/**
	 * @var IServiceManager Init daemon service manager
	 */
	private $initDaemon;

	/**
	 * @var array Init daemon service managers
	 */
	private $initDaemons = [
		'docker-supervisor' => DockerSupervisorManager::class,
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
			$this->initDaemon = new UnknownManager($commandManager);
		}
	}

	/**
	 * Start IQRF Gateway Daemon's service
	 * @return string Output from init daemon
	 * @throws NotSupportedInitSystemException
	 */
	public function start(): string {
		return $this->initDaemon->start();
	}

	/**
	 * Stop IQRF Gateway Daemon's service
	 * @return string Output from init daemon
	 * @throws NotSupportedInitSystemException
	 */
	public function stop(): string {
		return $this->initDaemon->stop();
	}

	/**
	 * Restart IQRF Gateway Daemon's service
	 * @return string Output from init daemon
	 * @throws NotSupportedInitSystemException
	 */
	public function restart(): string {
		return $this->initDaemon->restart();
	}

	/**
	 * Get status of IQRF Gateway Daemon's service
	 * @return string Output from init daemon
	 * @throws NotSupportedInitSystemException
	 */
	public function getStatus(): string {
		return $this->initDaemon->getStatus();
	}

}
