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
use App\ServiceModule\Models\ServiceManager;
use Nette\Utils\Strings;

/**
 * NTP manager
 */
class NtpManager {

	/**
	 * Used pool command pattern
	 */
	private const POOL_PATTERN = '/^(pool\s)(\S+)(\s.*)?$/';

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
	 * NTP service
	 */
	private const NTP_SERVICE = 'ntp';

	/**
	 * Timesyncd service
	 */
	private const TIMESYNCD_SERVICE = 'systemd-timesyncd';

	/**
	 * @var string $fullPath Path to configuration file
	 */
	private $fullPath;

	/**
	 * @var string $confFile Configuration file name
	 */
	private $confFile;

	/**
	 * @var CommandManager $commandManager Commang manager
	 */
	private $commandManager;

	/**
	 * @var PrivilegedFileManager $fileManager Privileged file manager
	 */
	private $fileManager;

	/**
	 * @var ServiceManager $serviceManager Service manager
	 */
	private $serviceManager;

	/**
	 * @var string $utility Time sync utility
	 */
	private $utility;

	/**
	 * Constructor
	 * @param FeatureManager $featureManager Feature manager
	 */
	public function __construct(CommandManager $commandManager, FeatureManager $featureManager, ServiceManager $serviceManager) {
		$feature = $featureManager->get('ntp');
		$this->fullPath = $feature['path'];
		$this->confFile = basename($this->fullPath);
		$this->commandManager = $commandManager;
		$this->fileManager = new PrivilegedFileManager(dirname($this->fullPath), $commandManager);
		$this->serviceManager = $serviceManager;
		$this->utility = $feature['utility'];
	}

	/**
	 * Returns NTP configuration
	 * @return array<int, string> NTP configuration
	 */
	public function readConfig(): array {
		if ($this->utility === 'timesyncd') {
			$config = $this->readTimesyncd();
			return explode(' ', $config['Time']['NTP']);
		}
		return $this->readNtp();
	}

	/**
	 * Stores NTP configuration
	 * @param array<string> $config NTP configuration
	 */
	public function storeConfig(array $config): void {
		if ($this->utility === 'timesyncd') {
			$this->storeTimesyncd($config);
		} else {
			$this->storeNtp($config);
		}
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
	 * Parses and returns NTP configuration from NTP service
	 * @return array<string> NTP configuration
	 */
	private function readNtp(): array {
		if (!file_exists($this->fullPath)) {
			throw new ConfNotFoundException('NTP cofiguration file not found.');
		}
		$pools = [];
		$config = explode(PHP_EOL, $this->fileManager->read($this->confFile));
		foreach ($config as $line) {
			$match = Strings::match($line, self::POOL_PATTERN);
			if ($match === null) {
				continue;
			}
			$pools[] = $match[2];
		}
		return $pools;
	}

	/**
	 * Converts and stores NTP configuration for timesyncd service
	 * @param array<string> $pools NTP configuration
	 */
	private function storeTimesyncd(array $pools): void {
		$current = $this->readTimesyncd();
		$current['Time']['NTP'] = implode(' ', $pools);
		$this->fileManager->write($this->confFile, ConfParser::toConf($current));
	}

	/**
	 * Converts and stores NTP configuration for NTP service
	 * @param array<string> $pools NTP configuration
	 */
	private function storeNtp(array $pools): void {
		if (!file_exists($this->fullPath)) {
			throw new ConfNotFoundException('NTP cofiguration file not found.');
		}
		$config = explode(PHP_EOL, $this->fileManager->read($this->confFile));
		$newConfig = [];
		foreach ($config as $line) {
			$match = Strings::match($line, self::POOL_PATTERN);
			if ($match === null) {
				$newConfig[] = $line;
				continue;
			}
			if (!in_array($match[1], $pools, true)) {
				continue;
			}
			$newConfig[] = $line;
			unset($pools[$match[1]]);
		}
		foreach ($pools as $pool) {
			$newConfig[] = 'pool ' . $pool . ' iburst';
		}
		$this->fileManager->write($this->confFile, implode(PHP_EOL, $newConfig));
	}

	/**
	 * Attempts to synchronize system clock
	 */
	public function sync(): void {
		if ($this->utility === 'timesyncd') {
			$this->serviceManager->restart(self::TIMESYNCD_SERVICE);
		} else {
			$this->serviceManager->stop(self::NTP_SERVICE);
			$command = $this->commandManager->run('ntpd -gq', true, 30);
			if ($command->getExitCode() !== 0) {
				throw new TimeDateException($command->getStderr());
			}
			$this->serviceManager->start(self::NTP_SERVICE);
		}
	}

}
