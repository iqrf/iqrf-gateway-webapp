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
use Nette\Utils\Strings;

class CommandManager {

	use Nette\SmartObject;

	/**
	 * @var bool
	 */
	private $sudo;

	/**
	 * Constructor
	 * @param bool $sudo Sudo required
	 */
	public function __construct($sudo) {
		$this->sudo = $sudo;
	}

	/**
	 * Execute shell command and return output
	 * @param string $cmd Command to execute
	 * @param bool $needSudo
	 * @return string Output
	 */
	public function send($cmd, $needSudo = false) {
		$command = $this->sudo && $needSudo ? 'sudo ' : '';
		$command .= $cmd;
		return Strings::trim(shell_exec($command));
	}

}
