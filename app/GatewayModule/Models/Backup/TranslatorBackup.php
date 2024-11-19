<?php

/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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
use App\CoreModule\Models\FileManager;
use Iqrf\CommandExecutor\CommandExecutor;

/**
 * Translator backup manager
 */
class TranslatorBackup extends IqrfSoftwareBackup {

	/**
	 * List of whitelisted files
	 */
	final public const WHITELIST = [
		'config.json',
	];

	/**
	 * Service names
	 */
	final public const SERVICES = [
		'iqrf-gateway-translator',
	];

	/**
	 * Constructor
	 * @param FileManager $fileManager File manager
	 * @param CommandExecutor $commandManager Command manager
	 * @param FeatureManager $featureManager Feature manager
	 * @param RestoreLogger $restoreLogger Restore logger
	 */
	public function __construct(
		FileManager $fileManager,
		CommandExecutor $commandManager,
		FeatureManager $featureManager,
		RestoreLogger $restoreLogger,
	) {
		parent::__construct(
			software: self::IQRF_GATEWAY_TRANSLATOR,
			fileManager: $fileManager,
			commandManager: $commandManager,
			featureManager: $featureManager,
			restoreLogger: $restoreLogger,
		);
	}

	/**
	 * Returns service names
	 * @return array<string> Service names
	 */
	public function getServices(): array {
		return $this->featureEnabled ? self::SERVICES : [];
	}

}
