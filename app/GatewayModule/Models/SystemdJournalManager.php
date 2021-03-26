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
use Nette\Utils\Strings;
use stdClass;

/**
 * Systemd journal manager
 */
class SystemdJournalManager {

	/**
	 * Paths to journald conf file
	 */
	private const CONF_FILE = '/data/lib/systemd/journald.conf.d/00-systemd-conf.conf';

	/**
	 * Journal configuration
	 */
	private const DEFAULT_CONFIG = [
		'ForwardToSyslog' => 'yes',
		'Storage' => 'volatile',
		'MaxFileSec' => '1week',
		'SystemMaxFiles' => '5',
        'SystemMaxFileSize' => '',
		'SystemMaxUse' => '64M',
	];

	/**
	 * Constructor
	 */
	public function __construct() {

	}

	/**
	 * Retrieves systemd journal configuration
	 * @return array<string, bool> Systemd journal configuration
	 */
	public function getConfig(): array {
		$conf = $this->getJournalConf();
		if (!array_key_exists('Journal', $conf)) {
			throw new InvalidConfFormatException('Journal section missing in configuration file.');
		}
		$journal = $conf['Journal'];
		return [
			'ForwardToSyslog' => $this->getPropertyDefault('ForwardToSyslog', $journal),
			'Storage' => $this->getStorage($journal),
			'MaxFileSec' => $this->getLogFileDuration($journal),
			'SystemMaxFiles' => $this->getMaxFiles($journal),
			'SystemMaxFileSize' => $this->getMaxFileSize($journal),
			'SystemMaxUse' => $this->getMaxDiskSize($journal),
		];
	}

	/**
	 * Parses Storage option
	 * @param array $conf Systemd journal configuration
	 * @return string Journal storage option method
	 */
	public function getStorage(array $conf): string {
		$storage = $this->getPropertyDefault('Storage', $conf);
		return $storage === 'persistent' ? 'persistent' : 'volatile';
	}

	/**
	 * Parses log file duration before rotation
	 * @param array $conf Systemd journal configuration
	 * @return array<string, int|string> Log file duration
	 */
	public function getLogFileDuration(array $conf): array {
		$duration = $this->getPropertyDefault('MaxFileSec', $conf);
		$pattern = '/^(\d+)(\w*$)/';
		$matches = Strings::match($duration, $pattern);
		return [
			'unit' => $matches[2] === '' ? 's' : $matches[2],
			'count' => intval($matches[1]),
		];
	}

	/**
	 * Parses maximum system log files option
	 * @param array $conf Systemd journal configuration
	 * @return int Maximum system log files
	 */
	public function getMaxFiles(array $conf): int {
		$files = $this->getPropertyDefault('SystemMaxFiles', $conf);
		return $files === '' ? 0 : intval($files);
	}

	/**
	 * Parses maximum system log file size in megabytes
	 * @param array $conf Systemd journal configuration
	 * @return int Maximum system log file size
	 */
	public function getMaxFileSize(array $conf): int {
		$size = $this->getPropertyDefault('SystemMaxFileSize', $conf);
		if ($size === '') {
			return 0;
		}
		$pattern = '/^(\d+)(\w*$)/';
		$matches = Strings::match($size, $pattern);
		return intval($matches[1]);
	}

	/**
	 * Parses maximum system log file size in megabytes
	 * @param array $conf Systemd journal configuration
	 * @return int Maximum systemd journal disk size
	 */
	public function getMaxDiskSize(array $conf): int {
		$size = $this->getPropertyDefault('SystemMaxUse', $conf);
		if ($size === '') {
			return 0;
		}
		$pattern = '/^(\d+)(\w*$)/';
		$matches = Strings::match($size, $pattern);
		return intval($matches[1]);
	}

	/**
	 * Returns key value if it exists, or default value otherwise
	 * @param string $key Configuration option
	 * @param array $conf Systemd journal configuration
	 * @return string Property value
	 */
	private function getPropertyDefault(string $key, array $conf): string {
		$property = self::DEFAULT_CONFIG[$key];
		if (array_key_exists($key, $conf)) {
			$property = $conf[$key];
		}
		return $property;
	}

	/**
	 * Stores new systemd journal configuration
	 * @param stdClass $newConf New systemd journal configuration
	 */
	private function saveConfig(stdClass $newConf): void {
		if (!file_exists(self::CONF_FILE)) {
			throw new ConfNotFoundException('Configuration file not found.');
		}
	}

	/**
	 * Reads systemd journal configuration
	 * @return array<string, array<string, mixed>> Systemd journal configuration
	 */
	private function getJournalConf(): array {
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
	 * Converts array to ini array
	 * @param array<string, array<string, mixed>> $conf Modified configuration array
	 * @return array<int, string> Configuration array in ini format
	 */
	private function toIni(array $conf): array {
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
