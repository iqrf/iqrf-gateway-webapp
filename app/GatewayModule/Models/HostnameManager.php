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
use App\CoreModule\Models\IFileManager;
use App\GatewayModule\Exceptions\HostnameException;
use Nette\IOException;
use Nette\Utils\Strings;

/**
 * Tool for hostname management
 */
class HostnameManager {

	/**
	 * @var string Hosts file name
	 */
	private const HOSTS_FILE = 'hosts';

	/**
	 * @var string Hostname file name
	 */
	private const HOSTNAME_FILE = 'hostname';

	/**
	 * @var CommandManager Command manager
	 */
	private CommandManager $commandManager;

	/**
	 * @var IFileManager File manager
	 */
	private IFileManager $fileManager;

	/**
	 * @var NetworkManager Network manager
	 */
	private NetworkManager $networkManager;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 * @param IFileManager $fileManager Privileged file manager
	 * @param NetworkManager $networkManager Network manager
	 */
	public function __construct(CommandManager $commandManager, IFileManager $fileManager, NetworkManager $networkManager) {
		$this->commandManager = $commandManager;
		$this->fileManager = $fileManager;
		$this->networkManager = $networkManager;
	}

	/**
	 * Sets new gateway hostname
	 * @param string $hostname Hostname to set
	 */
	public function setHostname(string $hostname): void {
		$old = $this->networkManager->getHostname();
		if ($old === '') {
				throw new HostnameException('Failed to retrieve hostname.');
		}
		try {
			$this->replaceHostname($old, $hostname);
		} catch (IOException $e) {
			throw new HostnameException($e->getMessage());
		}
		$output = $this->commandManager->run('hostname ' . $hostname, true);
		if ($output->getExitCode() !== 0) {
			$this->replaceHostname($hostname, $old);
			throw new HostnameException($output->getStderr());
		}
		$output = $this->commandManager->run('hostnamectl set-hostname ' . $hostname, true);
		if ($output->getExitCode() !== 0) {
			$this->replaceHostname($hostname, $old);
			throw new HostnameException($output->getStderr());
		}
	}


	/**
	 * Replaces hostname in hosts file
	 * @param string $oldHostname Hostname to replace
	 * @param string $newHostname New hostname
	 */
	private function replaceHostname(string $oldHostname, string $newHostname): void {
		$content = $this->fileManager->read(self::HOSTS_FILE);
		$ipv4Pattern = '/^.+\s+' . $oldHostname . '(\slocalhost)?$/';
		$ipv6Pattern = '/^.+\s+' . $oldHostname . '(\slocalhost)?(\sip6-localhost)?(\sip6-loopback)?$/';
		$lines = explode(PHP_EOL, $content);
		foreach ($lines as $idx => $line) {
			if (Strings::match($line, $ipv4Pattern) !== null) {
				$lines[$idx] = Strings::replace($line, '/' . $oldHostname . '/', $newHostname, 1);
			}
			if (Strings::match($line, $ipv6Pattern) !== null) {
				$lines[$idx] = Strings::replace($line, '/' . $oldHostname . '/', $newHostname, 1);
			}
		}
		$content = implode(PHP_EOL, $lines);
		$this->fileManager->write(self::HOSTS_FILE, $content);
		$this->fileManager->write(self::HOSTNAME_FILE, $newHostname);
	}

}
