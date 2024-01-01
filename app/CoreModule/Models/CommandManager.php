<?php

/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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
use Symfony\Component\Process\Exception\ProcessSignaledException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;
use Traversable;

/**
 * Tool for executing commands
 */
class CommandManager {

	/**
	 * Constructor
	 * @param bool $sudo Is sudo required?
	 * @param CommandStack $stack Command stack
	 */
	public function __construct(
		private readonly bool $sudo,
		private readonly CommandStack $stack,
	) {
	}

	/**
	 * Checks the existence of a command
	 * @param string $cmd Command
	 * @return bool Is the command exists?
	 */
	public function commandExist(string $cmd): bool {
		return $this->run('which ' . escapeshellarg($cmd))->getExitCode() === 0;
	}

	/**
	 * Executes shell command and returns output
	 * @param string $command Command to execute
	 * @param bool $needSudo Does the command need sudo?
	 * @param int $timeout Command's timeout
	 * @param string|int|float|bool|resource|Traversable|null $input Command's input
	 * @return ICommand Command entity
	 * @throws RuntimeException When process can't be launched
	 * @throws ProcessTimedOutException When process timed out
	 * @throws ProcessSignaledException When process stopped after receiving signal
	 */
	public function run(string $command, bool $needSudo = false, int $timeout = 60, mixed $input = null): ICommand {
		$process = $this->createProcess($command, $needSudo);
		$process->setInput($input);
		$process->setTimeout((float) $timeout);
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
	 * @param string|int|float|bool|resource|Traversable|null $input Command's input
	 */
	public function runAsync(callable $callback, string $command, bool $needSudo = false, int $timeout = 36000, mixed $input = null): void {
		$process = $this->createProcess($command, $needSudo);
		$process->setInput($input);
		$process->setTimeout((float) $timeout);
		$process->start($callback);
		$process->wait();
		$entity = new Command($command, $process);
		$this->stack->addCommand($entity);
	}

	/**
	 * Creates the process
	 * @param string $cmd Command to execute
	 * @param bool $needSudo Does the command need sudo?
	 * @return Process Created process
	 */
	private function createProcess(string $cmd, bool $needSudo): Process {
		$command = ($this->sudo && $needSudo ? 'sudo ' : '') . $cmd;
		$env = ['LANG' => 'C.UTF-8'];
		return Process::fromShellCommandline($command, null, $env);
	}

}
