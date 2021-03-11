<?php

/**
 * Copyright 2017-2021 MICRORISC s.r.o.
 * Copyright 2017-2021 IQRF Tech s.r.o.
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

use App\GatewayModule\Exceptions\ConfNotFoundException;
use App\GatewayModule\Exceptions\InvalidConfFormatException;
use Nette\Utils\FileSystem;

/**
 * Systemd log manager
 */
class SystemdLogManager {

	/**
	 * Paths to  journald conf file
	 */
	private const CONF_FILE = '/data/lib/systemd/journald.conf.d/00-systemd-conf.conf';

	/**
	 * Journal configuration persistence options
	 */
	private const PERSISTENCE = [
		'enabled' => 'persistent',
		'disabled' => 'none',
	];

	/**
	 * Retrieves systemd journal configuration
	 * @return array<string, bool> Systemd journal configuration
	 */
	public static function getConfig(): array {
		$conf = self::getJournalConf();
		if (!array_key_exists('Journal', $conf)) {
			throw new InvalidConfFormatException('Journal section missing in configuration file.');
		}
		$journal = $conf['Journal'];
		return [
			'persistent' => array_key_exists('Storage', $journal) ? ($journal['Storage'] === self::PERSISTENCE['enabled']) : false,
		];
	}

	/**
	 * Reads systemd journal configuration
	 * @return array<string, array<string, mixed>> Systemd journal configuration
	 */
	private static function getJournalConf(): array {
		if (!file_exists(self::CONF_FILE)) {
			throw new ConfNotFoundException('Configuration file not found.');
		}
		$conf = parse_ini_file(self::CONF_FILE, true, INI_SCANNER_RAW);
		if ($conf === false) {
			throw new InvalidConfFormatException('Invalid configuration file format.');
		}
		return $conf;
	}

	/**
	 * Changes journal persistence
	 * @param bool $enabled Should logs be persistent?
	 */
	public static function changePersistence(bool $enabled): void {
		$conf = self::getJournalConf();
		if (!array_key_exists('Journal', $conf)) {
			throw new InvalidConfFormatException('Journal section missing in configuration file.');
		}
		$conf['Journal']['Storage'] = $enabled ? self::PERSISTENCE['enabled'] : self::PERSISTENCE['disabled'];
		FileSystem::write(self::CONF_FILE, implode(PHP_EOL, self::toIni($conf)));
	}

	/**
	 * Converts array to ini array
	 * @param array<string, array<string, mixed>> $conf Modified configuration array
	 * @return array<int, string> Configuration array in ini format
	 */
	private static function toIni(array $conf): array {
		$output = [];
		foreach ($conf as $key => $value) {
			$output[] = '[' . $key . ']';
			foreach ($value as $childKey => $childValue) {
				$output[] = $childKey . '=' . $childValue;
			}
		}
		return $output;
	}

}
