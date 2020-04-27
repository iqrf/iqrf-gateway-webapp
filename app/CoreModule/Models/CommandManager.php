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

use App\CoreModule\Entities\Command;
use App\CoreModule\Entities\CommandStack;
use App\CoreModule\Entities\ICommand;
use Nette\SmartObject;
use Symfony\Component\Process\Process;

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
	 * @var CommandStack Command stack
	 */
	private $stack;

	/**
	 * Constructor
	 * @param bool $sudo Is sudo required?
	 * @param CommandStack $stack Command stack
	 */
	public function __construct(bool $sudo, CommandStack $stack) {
		$this->sudo = $sudo;
		$this->stack = $stack;
	}

	/**
	 * Checks the existence of a command
	 * @param string $cmd Command
	 * @return bool Is the command exists?
	 */
	public function commandExist(string $cmd): bool {
		return $this->run('which ' . $cmd)->getExitCode() === 0;
	}

	/**
	 * Creates the process
	 * @param string $cmd Command to execute
	 * @param bool $needSudo Does the command need sudo?
	 * @return Process Created process
	 */
	private function createProcess(string $cmd, bool $needSudo): Process {
		$command = ($this->sudo && $needSudo ? 'sudo ' : '') . $cmd;
		return Process::fromShellCommandline($command);
	}

	/**
	 * Executes shell command and returns output
	 * @param string $command Command to execute
	 * @param bool $needSudo Does the command need sudo?
	 * @param mixed $input Command's input
	 * @return ICommand Command entity
	 */
	public function run(string $command, bool $needSudo = false, $input = null): ICommand {
		$process = $this->createProcess($command, $needSudo);
		$process->setInput($input);
		$process->run();
		$entity = new Command($command, $process);
		$this->stack->addCommand($entity);
		return $entity;
	}

	/**
	 * Executes the command asynchronously
	 * @param callable $callback Callback to run whenever there is some output available on STDOUT or STDERR
	 * @param string $command Command to execute
	 * @param bool $needSudo Does the command need sudo?
	 * @param int $timeout Command's timeout
	 * @param mixed $input Command's input
	 */
	public function runAsync(callable $callback, string $command, bool $needSudo = false, int $timeout = 36000, $input = null): void {
		$process = $this->createProcess($command, $needSudo);
		$process->setInput($input);
		$process->setTimeout((float) $timeout);
		$process->start($callback);
		$process->wait();
		$entity = new Command($command, $process);
		$this->stack->addCommand($entity);
	}

}
