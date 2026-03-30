<?php

/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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

namespace App\GatewayModule\Models\Backup;

use App\CoreModule\Models\FeatureManager;
use Iqrf\CommandExecutor\CommandExecutor;
use Iqrf\FileManager\FileManager;

/**
 * Controller backup manager
 */
class ControllerBackup extends IqrfSoftwareBackup {

	/**
	 * List of whitelisted files
	 */
	public const WHITELIST = [
		'config.json',
	];

	/**
	 * Constructor
	 * @param FileManager $fileManager File manager
	 * @param CommandExecutor $commandExecutor Command manager
	 * @param FeatureManager $featureManager Feature manager
	 * @param RestoreLogger $restoreLogger Restore logger
	 */
	public function __construct(FileManager $fileManager, CommandExecutor $commandExecutor, FeatureManager $featureManager, RestoreLogger $restoreLogger) {
		parent::__construct(self::IQRF_GATEWAY_CONTROLLER, $fileManager, $commandExecutor, $featureManager, $restoreLogger);
	}

	/**
	 * Returns service names
	 * @return array<string> Service names
	 */
	public function getServices(): array {
		return $this->featureEnabled ? ['iqrf-gateway-controller'] : [];
	}

}
