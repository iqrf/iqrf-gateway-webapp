<?php

/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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

use App\CoreModule\Models\CommandManager;
use App\GatewayModule\Exceptions\HostnamectlException;
use Nette\Utils\FileSystem;
use Nette\Utils\Strings;

/**
 * Tool for hostname management
 */
class HostnameManager {

	/**
	 * Path to hosts file
	 */
	private const HOSTS_FILE = '/etc/hosts';

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
	 * Sets new gateway hostname
	 * @param string $hostname Hostname to set
	 */
	public function setHostname(string $hostname): void {
		$old = $this->getHostname();
		$output = $this->commandManager->run('hostnamectl set-hostname ' . $hostname, true);
		if ($output->getExitCode() !== 0) {
			throw new HostnamectlException($output->getStderr());
		}
		$this->replaceHostname($old, $hostname);
	}

	/**
	 * Returns current gateway hostname
	 * @return string Current gateway hostname
	 */
	private function getHostname(): string {
		$output = $this->commandManager->run('hostname -f');
		if ($output->getExitCode() !== 0) {
			throw new HostnamectlException($output->getStderr());
		}
		return $output->getStdout();
	}

	/**
	 * Replaces hostname in hosts file
	 * @param string $oldHostname Hostname to replace
	 * @param string $newHostname New hostname
	 */
	private function replaceHostname(string $oldHostname, string $newHostname): void {
		$content = FileSystem::read(self::HOSTS_FILE);
		$pattern = '/^.+\s+' . $oldHostname . '$/';
		$lines = explode(PHP_EOL, $content);
		foreach ($lines as $idx => $line) {
			if (Strings::match($line, $pattern) !== null) {
				$lines[$idx] = Strings::replace($line, '/' . $oldHostname . '/', $newHostname);
				break;
			}
		}
		$content = implode(PHP_EOL, $lines);
		FileSystem::write(self::HOSTS_FILE, $content);
	}

}
