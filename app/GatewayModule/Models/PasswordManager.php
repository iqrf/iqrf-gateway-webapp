<?php

/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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
use App\GatewayModule\Exceptions\ChpasswdErrorException;

/**
 * Gateway password manager
 */
class PasswordManager {

	/**
	 * @var CommandManager Command manager
	 */
	private CommandManager $commandManager;

	/**
	 * @var FeatureManager Feature manager
	 */
	private FeatureManager $featureManager;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 * @param FeatureManager $featureManager Feature manager
	 */
	public function __construct(CommandManager $commandManager, FeatureManager $featureManager) {
		$this->commandManager = $commandManager;
		$this->featureManager = $featureManager;
	}

	/**
	 * Change gateway account password
	 * @param string $password New password to set
	 */
	public function setPassword(string $password): void {
		$feature = $this->featureManager->get('gatewayPass');
		$input = $feature['user'] . ':' . $password;
		$command = $this->commandManager->run('chpasswd', true, 60, $input);
		if ($command->getExitCode() !== 0) {
			throw new ChpasswdErrorException($command->getStderr());
		}
	}

}
