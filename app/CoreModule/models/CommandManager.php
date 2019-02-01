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

namespace App\CoreModule\Models;

use Nette\SmartObject;
use Nette\Utils\Strings;
use Symfony\Component\Process\Process;
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
		$process = Process::fromShellCommandline($command);
		$process->run();
		$output = [
			'command' => $command,
			'stdout' => $process->getOutput(),
			'stderr' => $process->getErrorOutput(),
			'returnValue' => $process->getExitCode(),
		];
		Debugger::barDump($output, 'Command manager');
		return Strings::trim($output['stdout']);
	}

}
