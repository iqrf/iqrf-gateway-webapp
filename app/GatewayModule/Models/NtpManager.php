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
use App\CoreModule\Models\FeatureManager;
use App\GatewayModule\Exceptions\ConfNotFoundException;
use App\GatewayModule\Exceptions\InvalidConfFormatException;
use Nette\Utils\FileSystem;
use Nette\Utils\Strings;

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
	 * @var string $confPath Path to time sync conf file
	 */
	private $confPath;

	/**
	 * @var string $utility Time sync utility
	 */
	private $utility;

	/**
	 * Constructor
	 * @param FeatureManager $featureManager Feature manager
	 */
	public function __construct(FeatureManager $featureManager) {
		$feature = $featureManager->get('ntp');
		$this->utility = $feature['utility'];
		$this->confPath = $feature['path'];
	}

	/**
	 * Returns NTP configuration
	 * @return array<string, array<int, string>> NTP configuration
	 */
	public function readConfig(): array {
		if ($this->utility === 'timesyncd') {
			$config = $this->readTimesyncd();
			return ['servers' => explode(' ', $config['Time']['NTP'])];
		}
		$config = $this->readNtp();
		return [
			'servers' => array_map(function ($item): string {
				return $item[1];
			}, $config),
		];
	}

	/**
	 * Stores NTP configuration
	 * @param array<string, array<int, string>> $config NTP configuration
	 */
	public function storeConfig(array $config): void {
		if ($this->utility === 'timesyncd') {
			$this->storeTimesyncd($config);
		}
		$this->storeNtp($config);
	}

	/**
	 * Parses and returns NTP configuration from timesyncd service
	 * @return array<string, array<string, mixed>> NTP configuration
	 */
	private function readTimesyncd(): array {
		if (!file_exists($this->confPath)) {
			throw new ConfNotFoundException('Timesyncd configuration file not found.');
		}
		$config = ConfParser::toArray(FileSystem::read($this->confPath));
		if ($config === null) {
			throw new InvalidConfFormatException('Invalid configuration file format.');
		}
		return array_replace_recursive(self::TIMESYNCD_DEFAULT, $config);
	}

	/**
	 * Parses and returns NTP configuration from NTP service
	 * @return array<int, array<int, mixed>> NTP configuration
	 */
	private function readNtp(): array {
		if (!file_exists($this->confPath)) {
			throw new ConfNotFoundException('NTP cofiguration file not found.');
		}
		$servers = [];
		$config = explode(PHP_EOL, FileSystem::read($this->confPath));
		$serverPattern = '/^server\s(.*)$/';
		foreach ($config as $idx => $line) {
			$match = Strings::match($line, $serverPattern);
			if ($match === null) {
				continue;
			}
			$ip = filter_var($match[1], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
			$hostname = filter_var($match[1], FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);
			if ($match[1] === $ip || $match[1] === $hostname) {
				$servers[] = [$idx, $match[1]];
			}
		}
		return $servers;
	}

	/**
	 * Converts and stores NTP configuration for timesyncd service
	 * @param array<string, array<int, string>> $config NTP configuration
	 */
	private function storeTimesyncd(array $config): void {
		$current = $this->readTimesyncd();
		$current['Time']['NTP'] = implode(' ', $config['servers']);
		FileSystem::write($this->confPath, ConfParser::toConf($current));
	}

	/**
	 * Converts and stores NTP configuration for NTP service
	 */
	private function storeNtp(array $config): void {
		if (!file_exists($this->confPath)) {
			throw new ConfNotFoundException('NTP cofiguration file not found.');
		}
		$config = explode(PHP_EOL, FileSystem::read($this->confPath));
		$serverPattern = '/^server\s(.*)$/';
		$newConfig = [];
	}

}
