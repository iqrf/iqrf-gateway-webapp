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

namespace App\GatewayModule\Models\Backup;

use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\FeatureManager;

/**
 * Uploader backup manager
 */
class UploaderBackup extends IqrfSoftwareBackup {

	/**
	 * List of whitelisted files
	 */
	public const WHITELIST = [
		'config.json',
	];

	/**
	 * ZIP directory
	 */
	private const DIR = 'uploader';

	/**
	 * Feature name
	 */
	private const FEATURE = 'trUpload';

	/**
	 * Software name
	 */
	private const SOFTWARE = 'IQRF Gateway Uploader';

	/**
	 * Constructor
	 * @param string $path Path to Uploader configuration directory
	 * @param CommandManager $commandManager Command manager
	 * @param FeatureManager $featureManager Feature manager
	 * @param RestoreLogger $restoreLogger Restore logger
	 */
	public function __construct(string $path, CommandManager $commandManager, FeatureManager $featureManager, RestoreLogger $restoreLogger) {
		parent::__construct(self::SOFTWARE, self::DIR, self::FEATURE, $path, $commandManager, $featureManager, $restoreLogger);
	}

	/**
	 * Returns service names
	 * @return array<string> Service names
	 */
	public function getServices(): array {
		return [];
	}

}
