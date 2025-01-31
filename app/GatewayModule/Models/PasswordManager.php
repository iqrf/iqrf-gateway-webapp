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

namespace App\GatewayModule\Models;

use App\CoreModule\Models\FeatureManager;
use App\GatewayModule\Exceptions\ChpasswdErrorException;
use Iqrf\CommandExecutor\CommandExecutor;

/**
 * Gateway password manager
 */
class PasswordManager {

	/**
	 * Constructor
	 * @param CommandExecutor $commandManager Command manager
	 * @param FeatureManager $featureManager Feature manager
	 */
	public function __construct(
		private readonly CommandExecutor $commandManager,
		private readonly FeatureManager $featureManager,
	) {
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
