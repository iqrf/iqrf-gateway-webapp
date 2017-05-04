<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
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

namespace App\Model;

use Nette;
use Nette\NotImplementedException;

class ServiceManager {

	use Nette\SmartObject;

	/**
	 * @var string
	 * @inject
	 */
	private $initDaemon;

	/**
	 * @var bool
	 * @inject
	 */
	private $sudo;

	/**
	 * Constructor
	 * @param string $initDaemon Init daemon
	 * @param bool $sudo Sudo required
	 */
	public function __construct($initDaemon, $sudo) {
		$this->initDaemon = $initDaemon;
		//$this->sudo = $sudo;
		$this->sudo = false;
	}

	/**
	 * Start IQRF daemon
	 * @throws NotImplementedException
	 */
	public function start() {
		$cmd = $this->sudo ? 'sudo ' : '';
		switch ($this->initDaemon) {
			case 'systemd':
				$cmd .= 'systemctl start iqrf-daemon.service';
				break;
			default:
				throw new NotImplementedException();
		}
		return shell_exec($cmd);
	}

	/**
	 * Stop IQRF daemon
	 * @throws NotImplementedException
	 */
	public function stop() {
		$cmd = $this->sudo ? 'sudo ' : '';
		switch ($this->initDaemon) {
			case 'systemd':
				$cmd .= 'systemctl stop iqrf-daemon.service';
				break;
			default:
				throw new NotImplementedException();
		}
		return shell_exec($cmd);
	}

	/**
	 * Retart IQRF daemon
	 * @throws NotImplementedException
	 */
	public function restart() {
		$cmd = $this->sudo ? 'sudo ' : '';
		switch ($this->initDaemon) {
			case 'systemd':
				$cmd .= 'systemctl restart iqrf-daemon.service';
				break;
			default:
				throw new NotImplementedException();
		}
		return shell_exec($cmd);
	}

	/**
	 * Get status of IQRF daemon
	 * @throws NotImplementedException
	 */
	public function getStatus() {
		$cmd = $this->sudo ? 'sudo ' : '';
		switch ($this->initDaemon) {
			case 'systemd':
				$cmd .= 'systemctl status iqrf-daemon.service';
				break;
			default:
				throw new NotImplementedException();
		}
		return shell_exec($cmd);
	}

}
