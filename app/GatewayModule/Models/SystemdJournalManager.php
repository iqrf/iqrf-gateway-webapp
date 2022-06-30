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
use App\CoreModule\Models\FeatureManager;
use App\CoreModule\Models\PrivilegedFileManager;
use App\GatewayModule\Exceptions\ConfNotFoundException;
use App\GatewayModule\Exceptions\InvalidConfFormatException;
use Nette\Utils\Strings;
use stdClass;

/**
 * Systemd journal manager
 */
class SystemdJournalManager {

	/**
	 * Journal configuration
	 */
	private const DEFAULT_CONFIG = [
		'ForwardToSyslog' => 'no',
		'Storage' => 'volatile',
		'MaxFileSec' => '1month',
		'SystemMaxFiles' => '100',
		'SystemMaxFileSize' => '',
		'SystemMaxUse' => '',
	];

	/**
	 * @var string $confFile Journald conf file name
	 */
	private $confFile;

	/**
	 * @var PrivilegedFileManager $fileManager File manager
	 */
	private $fileManager;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 * @param FeatureManager $featureManager Feature manager
	 */
	public function __construct(CommandManager $commandManager, FeatureManager $featureManager) {
		$feature = $featureManager->get('systemdJournal');
		$path = $feature['path'];
		$this->confFile = basename($path);
		$this->fileManager = new PrivilegedFileManager(dirname($path), $commandManager);
	}

	/**
	 * Retrieves systemd journal configuration
	 * @return array<string, bool|int|string|array<string, int|string>> Systemd journal configuration
	 */
	public function getConfig(): array {
		$conf = $this->getJournalConf();
		if (!array_key_exists('Journal', $conf)) {
			throw new InvalidConfFormatException('Journal section missing in configuration file.');
		}
		$journal = $conf['Journal'];
		return [
			'forwardToSyslog' => $this->getPropertyDefault('ForwardToSyslog', $journal) === 'yes',
			'persistence' => $this->getStorage($journal),
			'maxDiskSize' => $this->getMaxDiskSize($journal),
			'maxFiles' => $this->getMaxFiles($journal),
			'sizeRotation' => $this->getSizeRotation($journal),
			'timeRotation' => $this->getTimeRotation($journal),
		];
	}

	/**
	 * Parses Storage option
	 * @param array<string, string> $conf Systemd journal configuration
	 * @return string Journal storage option method
	 */
	public function getStorage(array $conf): string {
		$storage = $this->getPropertyDefault('Storage', $conf);
		return $storage === 'persistent' ? 'persistent' : 'volatile';
	}

	/**
	 * Parses maximum system log file size in megabytes
	 * @param array<string, string> $conf Systemd journal configuration
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
	 * Parses maximum system log files option
	 * @param array<string, string> $conf Systemd journal configuration
	 * @return int Maximum system log files
	 */
	public function getMaxFiles(array $conf): int {
		$files = $this->getPropertyDefault('SystemMaxFiles', $conf);
		return $files === '' ? 0 : intval($files);
	}

	/**
	 * Parses maximum system log file size in megabytes
	 * @param array<string, string> $conf Systemd journal configuration
	 * @return array<string, int> Maximum system log file size
	 */
	public function getSizeRotation(array $conf): array {
		$size = $this->getPropertyDefault('SystemMaxFileSize', $conf);
		if ($size === '') {
			return ['maxFileSize' => 0];
		}
		$pattern = '/^(\d+)(\w*$)/';
		$matches = Strings::match($size, $pattern);
		return ['maxFileSize' => intval($matches[1])];
	}

	/**
	 * Parses log file duration before rotation
	 * @param array<string, string> $conf Systemd journal configuration
	 * @return array<string, int|string> Log file duration
	 */
	public function getTimeRotation(array $conf): array {
		$duration = $this->getPropertyDefault('MaxFileSec', $conf);
		$pattern = '/^(\d+)(\w*$)/';
		$matches = Strings::match($duration, $pattern);
		return [
			'unit' => $matches[2] === '' ? 's' : $matches[2],
			'count' => intval($matches[1]),
		];
	}

	/**
	 * Returns key value if it exists, or default value otherwise
	 * @param string $key Configuration option
	 * @param array<string, string> $conf Systemd journal configuration
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
	public function saveConfig(stdClass $newConf): void {
		if (!$this->fileManager->exists($this->confFile)) {
			throw new ConfNotFoundException('Configuration file not found.');
		}
		$conf = [
			'Journal' => [
				'ForwardToSyslog' => $newConf->forwardToSyslog ? 'yes' : 'no',
				'Storage' => $newConf->persistence,
				'MaxFileSec' => strval($newConf->timeRotation->count) . $newConf->timeRotation->unit,
				'SystemMaxUse' => $newConf->maxDiskSize === 0 ? '' : strval($newConf->maxDiskSize) . 'M',
				'SystemMaxFiles' => strval($newConf->maxFiles),
			],
		];
		if ($newConf->sizeRotation->maxFileSize !== 0) {
			$conf['Journal']['SystemMaxFileSize'] = strval($newConf->sizeRotation->maxFileSize) . 'M';
		}
		$this->fileManager->write($this->confFile, implode(PHP_EOL, $this->toIni($conf)) . PHP_EOL);
	}

	/**
	 * Reads systemd journal configuration
	 * @return array<string, array<string, mixed>> Systemd journal configuration
	 */
	private function getJournalConf(): array {
		if (!$this->fileManager->exists($this->confFile)) {
			throw new ConfNotFoundException('Configuration file not found.');
		}
		$conf = Strings::replace($this->fileManager->read($this->confFile), [
			'/^#/' => ';',
			'/\\n#/' => PHP_EOL . ';',
			'/\(/' => '"("',
			'/\)/' => '")"',
		]);
		$conf = parse_ini_string($conf, true, INI_SCANNER_RAW);
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
