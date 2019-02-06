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

namespace App\GatewayModule\Models;

use Nette\SmartObject;
use Symfony\Component\Process\Process;

/**
 * Tool for updating packages of IQRF Gateways
 */
class UpdaterManager {

	use SmartObject;

	/**
	 * @var bool Is sudo required?
	 */
	private $sudo;

	/**
	 * Constructor
	 * @param bool $sudo Is sudo required?
	 */
	public function __construct(bool $sudo) {
		$this->sudo = $sudo;
	}

	/**
	 * Updates a list of packages
	 * @param callable $callback Callback
	 */
	public function update(callable $callback): void {
		$this->runCommand('apt-get update', $callback);
	}

	/**
	 * Upgrades packages
	 * @param callable $callback Callback
	 */
	public function upgrade(callable $callback): void {
		$this->runCommand('apt-get upgrade -y', $callback);
	}

	/**
	 * Runs command in shell
	 * @param string $cmd Shell command
	 * @param callable $callback Callback
	 */
	private function runCommand(string $cmd, callable $callback): void {
		$command = ($this->sudo ? 'sudo ' : '') . $cmd;
		$process = Process::fromShellCommandline($command);
		$process->setTimeout(36000);
		$process->start($callback);
		$process->wait();
	}

}
