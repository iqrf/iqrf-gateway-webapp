<?php

/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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

namespace App\CoreModule\Entities;

/**
 * Interface for command entities
 */
interface ICommand {

	/**
	 * Returns the command
	 * @return string Command
	 */
	public function getCommand(): string;

	/**
	 * Returns the standard output
	 * @return string Standard output
	 */
	public function getStdout(): string;

	/**
	 * Returns the standard error output
	 * @return string Standard error output
	 */
	public function getStderr(): string;

	/**
	 * Returns the exit code
	 * @return int|null Exit code
	 */
	public function getExitCode(): ?int;

}
