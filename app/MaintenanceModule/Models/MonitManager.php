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

namespace App\MaintenanceModule\Models;

use App\CoreModule\Models\IFileManager;
use App\MaintenanceModule\Exceptions\MonitConfigErrorException;
use Nette\Utils\Strings;

/**
 * Monit manager
 */
class MonitManager {

	/**
	 * @var IFileManager $fileManager File manager
	 */
	private $fileManager;

	/**
	 * Monit configuration file
	 */
	private const CONF_FILE = 'monitrc';

	/**
	 * Constructor
	 * @param IFileManager $fileManager Privileged file manager
	 */
	public function __construct(IFileManager $fileManager) {
		$this->fileManager = $fileManager;
	}

	/**
	 * Parses configuration file and returns configuration as array
	 * @return array<string, string> Monit configuration array
	 */
	public function getConfig(): array {
		$configArray = explode(PHP_EOL, $this->readConfig());
		$configArray = array_filter($configArray, function ($item): bool {
			return !Strings::startsWith($item, '#');
		});
		$pattern = '/^(set\smmonit\shttps?:\/\/)(\w+)(\:)(\w+)(@)([0-9a-zA-Z\-\_\.\/]+)$/';
		foreach ($configArray as $item) {
			$matches = Strings::match($item, $pattern);
			if ($matches !== null) {
				break;
			}
		}
		if (!isset($matches)) {
			throw new MonitConfigErrorException('Monit configuration file contains invalid content.');
		}
		return [
			'endpoint' => $matches[6],
			'username' => $matches[2],
			'password' => $matches[4],
		];
	}

	/**
	 * Saves new monit configuration
	 * @param array<string, string> $newConfig New monit configuration
	 */
	public function saveConfig(array $newConfig): void {
		$configArray = explode(PHP_EOL, $this->readConfig());
		$pattern = '/^set\smmonit\shttps?:\/\/\w+\:\w+@[0-9a-zA-Z\-\_\.\/]+$/';
		$idx = -1;
		foreach ($configArray as $key => $val) {
			$matches = Strings::match($val, $pattern);
			if ($matches !== null) {
				$idx = $key;
				break;
			}
		}
		if ($idx === -1) {
			throw new MonitConfigErrorException('Monit configuration file contains invalid content.');
		}
		$configArray[$idx] = sprintf('set mmonit https://%s:%s@%s', $newConfig['username'], $newConfig['password'], $newConfig['endpoint']);
		$this->fileManager->write(self::CONF_FILE, implode(PHP_EOL, $configArray));
	}

	/**
	 * Reads monit configuration file
	 * @return string Monit configuration
	 */
	public function readConfig(): string {
		return $this->fileManager->read(self::CONF_FILE);
	}

}
