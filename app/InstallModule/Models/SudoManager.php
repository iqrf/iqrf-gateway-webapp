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

namespace App\InstallModule\Models;

use App\CoreModule\Models\CommandManager;

/**
 * Sudo manager
 */
class SudoManager {

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 */
	public function __construct(CommandManager $commandManager) {
		$this->commandManager = $commandManager;
	}

	/**
	 * Checks if sudo exists and webapp can use sudo
	 * @return array<string, bool|string> Sudo check meta
	 */
	public function checkSudo(): array {
		$user = posix_getpwuid(posix_geteuid())['name'];
		if ($user !== 'root') {
			return [
				'user' => $user,
				'exists' => $this->commandManager->commandExist('sudo'),
				'userSudo' => $this->commandManager->run('sudo -v')->getExitCode() === 0,
			];
		}
		return [];
	}

}
