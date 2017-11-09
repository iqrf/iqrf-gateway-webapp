<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017 IQRF Tech s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace App\ServiceModule\Model;

use App\Model\CommandManager;
use App\ServiceModule\Model\NotSupportedInitSystemException;
use Nette;

class ServiceManager {

	use Nette\SmartObject;

	/**
	 * @var string
	 */
	private $initDaemon;

	/**
	 * @var CommandManager
	 */
	private $commandManager;

	/**
	 * Constructor
	 * @param string $initDaemon Init daemon
	 * @param CommandManager $commandManager Command Managerd
	 */
	public function __construct($initDaemon, CommandManager $commandManager) {
		$this->initDaemon = $initDaemon;
		$this->commandManager = $commandManager;
	}

	/**
	 * Start IQRF daemon
	 * @throws NotSupportedInitSystemException
	 */
	public function start() {
		switch ($this->initDaemon) {
			case 'docker-supervisor':
				$cmd = 'supervisorctl start iqrf-daemon';
				break;
			case 'systemd':
				$cmd = 'systemctl start iqrf-daemon.service';
				break;
			default:
				throw new NotSupportedInitSystemException();
		}
		return $this->commandManager->send($cmd, true);
	}

	/**
	 * Stop IQRF daemon
	 * @throws NotSupportedInitSystemException
	 */
	public function stop() {
		switch ($this->initDaemon) {
			case 'docker-supervisor':
				$cmd = 'supervisorctl stop iqrf-daemon';
				break;
			case 'systemd':
				$cmd = 'systemctl stop iqrf-daemon.service';
				break;
			default:
				throw new NotSupportedInitSystemException();
		}
		return $this->commandManager->send($cmd, true);
	}

	/**
	 * Retart IQRF daemon
	 * @throws NotSupportedInitSystemException
	 */
	public function restart() {
		switch ($this->initDaemon) {
			case 'docker-supervisor':
				$cmd = 'supervisorctl restart iqrf-daemon';
				break;
			case 'systemd':
				$cmd = 'systemctl restart iqrf-daemon.service';
				break;
			default:
				throw new NotSupportedInitSystemException();
		}
		return $this->commandManager->send($cmd, true);
	}

	/**
	 * Get status of IQRF daemon
	 * @throws NotSupportedInitSystemException
	 */
	public function getStatus() {
		switch ($this->initDaemon) {
			case 'docker-supervisor':
				$cmd = 'supervisorctl status iqrf-daemon';
				break;
			case 'systemd':
				$cmd = 'systemctl status iqrf-daemon.service';
				break;
			default:
				throw new NotSupportedInitSystemException();
		}
		return $this->commandManager->send($cmd, true);
	}

}
