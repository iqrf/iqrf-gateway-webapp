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

namespace App\ConfigModule\Models;

use App\ConfigModule\Exceptions\AptErrorException;
use App\ConfigModule\Exceptions\AptNotFoundException;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\IFileManager;

class AptManager {

	/**
	 * Default values
	 */
	private const DEFAULTS = [
		'APT::Periodic::Enable' => '0',
		'APT::Periodic::Update-Package-Lists' => '1',
		'APT::Periodic::Unattended-Upgrade' => '1',
		'APT::Periodic::AutocleanInterval' => '0',
		'Unattended-Upgrade::Automatic-Reboot' => 'false',
	];

	/**
	 * Apt configuration file name
	 */
	private const FILE_NAME = '99iqrf-gateway-webapp';

	/**
	 * Constructor
	 * @param IFileManager $fileManager Privileged file manager
	 * @param CommandManager $commandManager Command manager
	 */
	public function __construct(
		private readonly IFileManager $fileManager,
		private readonly CommandManager $commandManager,
	) {
	}

	/**
	 * Reads APT configuration
	 * @return array<string, string> APT configuration
	 * @throws AptErrorException
	 * @throws AptNotFoundException
	 */
	public function read(): array {
		if (!$this->commandManager->commandExist('apt-config')) {
			throw new AptNotFoundException('Apt package not installed.');
		}
		$command = $this->commandManager->run('apt-config dump', false);
		if ($command->getExitCode() !== 0) {
			throw new AptErrorException('An error has occurred while retrieving apt configuration');
		}
		$config = [];
		$rows = explode(PHP_EOL, $command->getStdout());
		foreach ($rows as $row) {
			[$key, $value] = explode(' ', rtrim($row, ';'), 2);
			if (!array_key_exists($key, self::DEFAULTS)) {
				continue;
			}
			$config[$key] = trim($value, '"');
		}
		return array_merge(self::DEFAULTS, $config);
	}

	/**
	 * Writes APT configuration
	 * @param array<string, string> $newConfig APT configuration to write
	 * @throws AptErrorException
	 * @throws AptNotFoundException
	 */
	public function write(array $newConfig): void {
		$config = $this->read();
		$content = '';
		foreach ($newConfig as $key => $value) {
			if (!array_key_exists($key, self::DEFAULTS)) {
				continue;
			}
			$config[$key] = $value;
		}
		foreach ($config as $key => $value) {
			$content .= sprintf('%s "%s";', $key, $value);
		}
		$this->fileManager->write(self::FILE_NAME, $content);
	}

}
