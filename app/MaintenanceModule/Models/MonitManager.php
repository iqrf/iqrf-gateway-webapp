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

namespace App\MaintenanceModule\Models;

use App\CoreModule\Models\IFileManager;
use App\MaintenanceModule\Exceptions\MonitConfigErrorException;
use Nette\Utils\Strings;

/**
 * Monit manager
 */
class MonitManager {

	/**
	 * @var string Monit configuration file
	 */
	private const CONF_FILE = 'monitrc';

	/**
	 * @var string Server and credentials pattern
	 */
	private const PATTERN = '/^set\smmonit\shttps?:\/\/(?\'user\'\w+):(?\'password\'\w+)@(?\'endpoint\'.+)$/';

	/**
	 * Constructor
	 * @param IFileManager $fileManager Privileged file manager
	 */
	public function __construct(
		private readonly IFileManager $fileManager,
	) {
	}

	/**
	 * Parses configuration file and returns configuration as array
	 * @return array{endpoint: string, username: string, password: string} Monit configuration array
	 * @throws MonitConfigErrorException
	 */
	public function getConfig(): array {
		$configArray = explode(PHP_EOL, $this->readConfig());
		$configArray = array_filter(
			$configArray,
			static fn (string $item): bool => !str_starts_with($item, '#')
		);
		foreach ($configArray as $item) {
			$matches = Strings::match($item, self::PATTERN);
			if ($matches !== null) {
				break;
			}
		}
		if (!isset($matches)) {
			throw new MonitConfigErrorException('Monit configuration file contains invalid content.');
		}
		return [
			'endpoint' => $matches['endpoint'],
			'username' => $matches['user'],
			'password' => $matches['password'],
		];
	}

	/**
	 * Saves new monit configuration
	 * @param array{endpoint: mixed, username: mixed, password: mixed} $newConfig New monit configuration
	 * @throws MonitConfigErrorException
	 */
	public function saveConfig(array $newConfig): void {
		$configArray = explode(PHP_EOL, $this->readConfig());
		$idx = -1;
		foreach ($configArray as $key => $val) {
			$matches = Strings::match($val, self::PATTERN);
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
