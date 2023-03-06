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

namespace App\GatewayModule\Models\BoardManagers;

use App\CoreModule\Models\CommandManager;

/**
 * DMI board manager
 */
class DmiBoardManager implements IBoardManager {

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 */
	public function __construct(
		private readonly CommandManager $commandManager,
	) {
	}

	/**
	 * Get the board's name from DMI
	 * @return string|null Board's name
	 */
	public function getName(): ?string {
		$vendor = $this->commandManager->run('cat /sys/class/dmi/id/board_vendor', true)->getStdout();
		$name = $this->commandManager->run('cat /sys/class/dmi/id/board_name', true)->getStdout();
		$version = $this->commandManager->run('cat /sys/class/dmi/id/board_version', true)->getStdout();
		if ($name !== '' && $vendor !== '') {
			$versionStr = $version === '' ? '' : ' (' . $version . ')';
			return $vendor . ' ' . $name . $versionStr;
		}
		return null;
	}

}
