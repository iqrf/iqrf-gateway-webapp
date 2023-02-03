<?php

/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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

namespace Tests\Stubs\CoreModule\Models;

use App\CoreModule\Entities\ICommand;

/**
 * Command entity stub
 */
final class Command implements ICommand {

	/**
	 * @var string Command
	 */
	private string $command;

	/**
	 * @var string Standard output
	 */
	private string $stdout;

	/**
	 * @var string Standard error output
	 */
	private string $stderr;

	/**
	 * @var int|null Exit code
	 */
	private ?int $exitCode;

	/**
	 * Constructor
	 * @param string $command Command
	 * @param string $stdout Standard output
	 * @param string $stderr Standard error output
	 * @param int|null $exitCode Exit code
	 */
	public function __construct(string $command, string $stdout, string $stderr, ?int $exitCode) {
		$this->command = $command;
		$this->stdout = $stdout;
		$this->stderr = $stderr;
		$this->exitCode = $exitCode;
	}

	/**
	 * Returns the command
	 * @return string Command
	 */
	public function getCommand(): string {
		return $this->command;
	}

	/**
	 * Returns the standard output
	 * @return string Standard output
	 */
	public function getStdout(): string {
		return $this->stdout;
	}

	/**
	 * Returns the standard error output
	 * @return string Standard error output
	 */
	public function getStderr(): string {
		return $this->stderr;
	}

	/**
	 * Returns the exit code
	 * @return int|null Exit code
	 */
	public function getExitCode(): ?int {
		return $this->exitCode;
	}

}
