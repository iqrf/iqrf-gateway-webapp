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

namespace App\CoreModule\Models;

use Nette\SmartObject;
use Nette\Utils\Strings;
use Tracy\Debugger;

/**
 * Tool for executing commands
 */
class CommandManager {

	use SmartObject;

	/**
	 * @var bool Is sudo required?
	 */
	private $sudo;

	/**
	 * @var mixed[] An indexed array where the key represents the descriptor number and the value represents how PHP will pass that descriptor to the child process. 0 is stdin, 1 is stdout, while 2 is stderr.
	 */
	private $descriptorspec = [
		0 => ['pipe', 'r'], // stdin is a pipe that the child will read from
		1 => ['pipe', 'w'], // stdout is a pipe that the child will write to
		2 => ['pipe', 'w'], // stderr is a pipe that the child will write to
	];

	/**
	 * Constructor
	 * @param bool $sudo Is sudo required?
	 */
	public function __construct(bool $sudo) {
		$this->sudo = $sudo;
	}

	/**
	 * Check the existence of a command
	 * @param string $cmd Command
	 * @return bool Is the command exists?
	 */
	public function commandExist(string $cmd): bool {
		return $this->send('which ' . $cmd) !== '';
	}

	/**
	 * Execute shell command and return output
	 * @param string $cmd Command to execute
	 * @param bool $needSudo Is the command need sudo?
	 * @return string Output
	 */
	public function send(string $cmd, bool $needSudo = false): string {
		$command = ($this->sudo && $needSudo ? 'sudo ' : '') . $cmd;
		$output = $pipes = [];
		$output['command'] = $command;
		$process = proc_open($command, $this->descriptorspec, $pipes);
		if (is_resource($process)) {
			fclose($pipes[0]);
			$output['stdout'] = stream_get_contents($pipes[1]);
			fclose($pipes[1]);
			$output['stderr'] = stream_get_contents($pipes[2]);
			fclose($pipes[2]);
			// It is important that you close any pipes before calling
			// proc_close in order to avoid a deadlock
			$output['returnValue'] = proc_close($process);
		}
		Debugger::barDump($output, 'Command manager');
		return Strings::trim($output['stdout']);
	}

}
