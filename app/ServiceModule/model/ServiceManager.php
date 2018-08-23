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
use Nette;

/**
 * Tool for managing services
 */
class ServiceManager {

	use Nette\SmartObject;

	/**
	 * @var string Init daemon
	 */
	private $initDaemon;

	/**
	 * @var string Name of service
	 */
	private $serviceName = 'iqrfgd2';

	/**
	 * @var CommandManager Command Manager
	 */
	private $commandManager;

	/**
	 * Constructor
	 * @param string $initDaemon Init daemon
	 * @param CommandManager $commandManager Command manager
	 */
	public function __construct(string $initDaemon, CommandManager $commandManager) {
		$this->initDaemon = $initDaemon;
		$this->commandManager = $commandManager;
	}

	/**
	 * Start IQRF Gateway Daemon
	 * @throws NotSupportedInitSystemException
	 */
	public function start() {
		switch ($this->initDaemon) {
			case 'docker-supervisor':
				$cmd = 'supervisorctl start ' . $this->serviceName;
				break;
			case 'systemd':
				$cmd = 'systemctl start ' . $this->serviceName . '.service';
				break;
			default:
				throw new NotSupportedInitSystemException();
		}
		return $this->commandManager->send($cmd, true);
	}

	/**
	 * Stop IQRF Gateway Daemon
	 * @throws NotSupportedInitSystemException
	 */
	public function stop() {
		switch ($this->initDaemon) {
			case 'docker-supervisor':
				$cmd = 'supervisorctl stop ' . $this->serviceName;
				break;
			case 'systemd':
				$cmd = 'systemctl stop ' . $this->serviceName . '.service';
				break;
			default:
				throw new NotSupportedInitSystemException();
		}
		return $this->commandManager->send($cmd, true);
	}

	/**
	 * Retart IQRF Gateway Daemon
	 * @throws NotSupportedInitSystemException
	 */
	public function restart() {
		switch ($this->initDaemon) {
			case 'docker-supervisor':
				$cmd = 'supervisorctl restart ' . $this->serviceName;
				break;
			case 'systemd':
				$cmd = 'systemctl restart ' . $this->serviceName . '.service';
				break;
			default:
				throw new NotSupportedInitSystemException();
		}
		return $this->commandManager->send($cmd, true);
	}

	/**
	 * Get status of IQRF Gateway Daemon
	 * @throws NotSupportedInitSystemException
	 */
	public function getStatus() {
		switch ($this->initDaemon) {
			case 'docker-supervisor':
				$cmd = 'supervisorctl status ' . $this->serviceName;
				break;
			case 'systemd':
				$cmd = 'systemctl status ' . $this->serviceName . '.service';
				break;
			default:
				throw new NotSupportedInitSystemException();
		}
		return $this->commandManager->send($cmd, true);
	}

}
