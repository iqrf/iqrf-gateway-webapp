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

use App\ConfigModule\Utils\ConfParser;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\FeatureManager;
use App\CoreModule\Models\PrivilegedFileManager;
use App\GatewayModule\Exceptions\ConfNotFoundException;
use App\GatewayModule\Exceptions\InvalidConfFormatException;
use App\GatewayModule\Exceptions\TimeDateException;

/**
 * NTP manager
 */
class NtpManager {

	/**
	 * Default timesyncd configuration
	 */
	private const TIMESYNCD_DEFAULT = [
		'Time' => [
			'NTP' => '',
			'FallbackNTP' => '',
			'RootDistanceMaxSec' => 5,
			'PollIntervalMinSec' => 32,
			'PollIntervalMaxSec' => 2048,
		],
	];

	/**
	 * @var string $fullPath Path to configuration file
	 */
	private string $fullPath;

	/**
	 * @var string $confFile Configuration file name
	 */
	private string $confFile;

	/**
	 * @var CommandManager $commandManager Commang manager
	 */
	private CommandManager $commandManager;

	/**
	 * @var PrivilegedFileManager $fileManager Privileged file manager
	 */
	private PrivilegedFileManager $fileManager;

	/**
	 * Constructor
	 * @param FeatureManager $featureManager Feature manager
	 */
	public function __construct(CommandManager $commandManager, FeatureManager $featureManager) {
		$feature = $featureManager->get('ntp');
		$this->fullPath = $feature['path'];
		$this->confFile = basename($this->fullPath);
		$this->commandManager = $commandManager;
		$this->fileManager = new PrivilegedFileManager(dirname($this->fullPath), $commandManager);
	}

	/**
	 * Returns NTP configuration
	 * @return array<int, string> NTP configuration
	 */
	public function readConfig(): array {
		$config = $this->readTimesyncd();
		$servers = $config['Time']['NTP'];
		if (strlen($servers) === 0) {
			return [];
		}
		return explode(' ', $config['Time']['NTP']);
	}

	/**
	 * Stores NTP configuration
	 * @param array<string> $config NTP configuration
	 */
	public function storeConfig(array $config): void {
		$this->storeTimesyncd($config);
	}

	/**
	 * Parses and returns NTP configuration from timesyncd service
	 * @return array<string, array<string, mixed>> NTP configuration
	 */
	private function readTimesyncd(): array {
		if (!file_exists($this->fullPath)) {
			throw new ConfNotFoundException('Timesyncd configuration file not found.');
		}
		$config = ConfParser::toArray($this->fileManager->read($this->confFile));
		if ($config === null) {
			throw new InvalidConfFormatException('Invalid configuration file format.');
		}
		return array_replace_recursive(self::TIMESYNCD_DEFAULT, $config);
	}

	/**
	 * Converts and stores NTP configuration for timesyncd service
	 * @param array<string> $pools NTP configuration
	 */
	private function storeTimesyncd(array $pools): void {
		$current = $this->readTimesyncd();
		$servers = implode(' ', $pools);
		if (strlen($servers) === 0) {
			$current['Time']['NTP'] = null;
		} else {
			$current['Time']['NTP'] = $servers;
		}
		$this->fileManager->write($this->confFile, ConfParser::toConf($current));
	}

	/**
	 * Attempts to synchronize system clock
	 */
	public function sync(): void {
		$command = $this->commandManager->run('timedatectl set-ntp false', true);
		if ($command->getExitCode() !== 0) {
			throw new TimeDateException($command->getStderr());
		}
		$command = $this->commandManager->run('timedatectl set-ntp true', true);
		if ($command->getExitCode() !== 0) {
			throw new TimeDateException($command->getStderr());
		}
		$timeout = 30000000;
		while ($timeout > 0) {
			if (file_exists('/run/systemd/timesync/synchronized')) {
				break;
			}
			$timeout -= 100000;
			usleep(100000);
		}
		if ($timeout === 0) {
			throw new TimeDateException('Network time synchronization failed.');
		}
	}

}
