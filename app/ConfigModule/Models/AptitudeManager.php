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

namespace App\ConfigModule\Models;

use App\ConfigModule\Exceptions\AptErrorException;
use App\ConfigModule\Exceptions\AptNotFoundException;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\FileManager;

class AptitudeManager {

	/**
	 * Path to aptitude configuration directory
	 */
	private const PATH = '/etc/apt/apt.conf.d/';

	/**
	 * Aptitude configuration file name
	 */
	private const FILE_NAME = '99iqrf-gateway-webapp';

	/**
	 * Aptitude unattended upgrades tree path
	 */
	private const APT_SETTING = 'APT::Periodic::Enable';

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * @var FileManager File manager
	 */
	private $fileManager;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 */
	public function __construct(CommandManager $commandManager) {
		$this->commandManager = $commandManager;
		$this->fileManager = new FileManager(self::PATH, $commandManager);
	}

	/**
	 * Retrieves enabled status of unattended upgrades service
	 */
	public function getEnable(): bool {
		$config = $this->listAptConf();
		foreach ($config as $entry) {
			if (substr($entry, 0, strlen(self::APT_SETTING)) === self::APT_SETTING) {
				$value = explode(' ', $entry)[1][1];
				return $value === '1';
			}
		}
		return false;
	}

	/**
	 * Sets enabled status of unattended upgrades service
	 * @param bool $enable New settings
	 * @return bool updated setting
	 */
	public function setEnable(bool $enable): bool {
		$setting = $enable ? '"1";' : '"0";';
		if (!file_exists(self::PATH . self::FILE_NAME)) {
			$this->createConfFile();
		}
		$this->fileManager->write(self::FILE_NAME, self::APT_SETTING . ' ' . $setting . "\n");
		return $enable;
	}

	/**
	 * Creates apt configuration file and sets permission for webapp
	 */
	public function createConfFile(): void {
		$this->commandManager->run('touch ' . self::PATH . self::FILE_NAME, true);
		$this->commandManager->run('chmod 666 ' . self::PATH . self::FILE_NAME, true);
	}

	/**
	 * Returns list of aptitude configuration
	 * @return array<string> Aptitude configuration
	 */
	public function listAptConf(): array {
		if (!$this->commandManager->commandExist('apt-config')) {
			throw new AptNotFoundException('Aptitude package not installed.');
		}
		$conf = $this->commandManager->run('apt-config dump | grep ' . self::APT_SETTING, false);
		if ($conf->getExitCode() !== 0) {
			throw new AptErrorException('An error has occured while retrieving aptitude configuration');
		}
		$array = explode("\n", $conf->getStdout());
		return $array;
	}

}
