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

	public function readConfig(): array {
		if ($this->utility === 'timesyncd') {
			return $this->readTimesync();
		} 
		return $this->readNtp();
	}

	private function readTimesync(): array {
		if (!file_exists($this->confPath)) {
			throw new ConfNotFoundException('Timesyncd configuration file not found.');
		}
		$config = ConfParser::toArray(FileSystem::read($this->confPath));
		if ($config === null) {
			throw new InvalidConfFormatException('Invalid configuration file format.');
		}
		$config = array_merge_recursive(self::TIMESYNCD_DEFAULT, $config);
		return ['servers' => explode(' ', $config['Time']['NTP'])];
	}

	private function readNtp(): array {
		if (!file_exists($this->confPath)) {
			throw new ConfNotFoundException('NTP cofiguration file not found.');
		}
		return [];
	}

}
