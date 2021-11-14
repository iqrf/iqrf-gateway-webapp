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

use App\ConfigModule\Models\MainManager;

/**
 * IQRF Gateway daemon directory manager
 */
class DaemonDirectories {

	/**
	 * @var string Path to a directory with IQRF Gateway Daemon's cache
	 */
	private $cacheDir;

	/**
	 * @var string Path to a directory with IQRF Gateway Daemon's configuration
	 */
	private $configurationDir;

	/**
	 * @var string Path to a directory with IQRF Gateway Daemon's data
	 */
	private $dataDir;

	/**
	 * @var string Path to a directory with log files of IQRF Gateway Daemon
	 */
	private $logDir;

	/**
	 * Constructor
	 * @param string $confDir Path to a directory with IQRF Gateway Daemon's configuration
	 * @param string $logDir Path to a directory with log files of IQRF Gateway Daemon
	 * @param MainManager $mainManager Main configuration manager
	 */
	public function __construct(string $confDir, string $logDir, MainManager $mainManager) {
		$this->cacheDir = $mainManager->getCacheDir();
		$this->configurationDir = $confDir;
		$this->dataDir = $mainManager->getDataDir();
		$this->logDir = $logDir;
	}

	/**
	 * Returns the path of cache directory
	 * @return string Cache directory path
	 */
	public function getCacheDir(): string {
		return $this->cacheDir;
	}

	/**
	 * Returns the path of TLS certificates directory
	 * @return string TLS certificates directory path
	 */
	public function getCertDir(): string {
		return $this->configurationDir . '/certs/';
	}

	/**
	 * Returns the path of configuration directory
	 * @return string Configuration directory path
	 */
	public function getConfigurationDir(): string {
		return $this->configurationDir;
	}

	/**
	 * Returns the path of data directory
	 * @return string Data directory path
	 */
	public function getDataDir(): string {
		return $this->dataDir;
	}

	/**
	 * Returns the path of log directory
	 * @return string Log directory path
	 */
	public function getLogDir(): string {
		return $this->logDir;
	}

}
