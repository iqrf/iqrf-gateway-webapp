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
use App\ServiceModule\Exceptions\NotSupportedInitSystemException;
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
	 * @var string[] Init daemon service managers
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
		$this->initDaemon = array_key_exists($initDaemon, $this->initDaemons) ?
			new $this->initDaemons[$initDaemon]($commandManager) :
			new UnknownManager($commandManager);
	}

	/**
	 * Starts IQRF Gateway Daemon's service
	 * @return string Output from init daemon
	 * @throws NotSupportedInitSystemException
	 */
	public function start(): string {
		return $this->initDaemon->start();
	}

	/**
	 * Stops IQRF Gateway Daemon's service
	 * @return string Output from init daemon
	 * @throws NotSupportedInitSystemException
	 */
	public function stop(): string {
		return $this->initDaemon->stop();
	}

	/**
	 * Restarts IQRF Gateway Daemon's service
	 * @return string Output from init daemon
	 * @throws NotSupportedInitSystemException
	 */
	public function restart(): string {
		return $this->initDaemon->restart();
	}

	/**
	 * Gets status of IQRF Gateway Daemon's service
	 * @return string Output from init daemon
	 * @throws NotSupportedInitSystemException
	 */
	public function getStatus(): string {
		return $this->initDaemon->getStatus();
	}

}
