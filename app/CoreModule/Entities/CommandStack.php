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

namespace App\CoreModule\Entities;

/**
 * Command stack entity
 */
class CommandStack {

	/**
	 * @var array<Command> Commands
	 */
	private array $commands = [];

	/**
	 * Adds a command in to the stack
	 * @param Command $command Command to add
	 */
	public function addCommand(Command $command): void {
		$this->commands[] = $command;
	}

	/**
	 * Returns commands in the stack
	 * @return array<Command> Commands in the stack
	 */
	public function getCommands(): array {
		return $this->commands;
	}

}
