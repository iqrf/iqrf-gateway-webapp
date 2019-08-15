<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2019 IQRF Tech s.r.o.
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

namespace App\CloudModule\Models;

use App\CloudModule\Enums\PixlaService;
use App\CoreModule\Models\CommandManager;

/**
 * PIXLA management system manager
 */
class PixlaManager {

	/**
	 * SystemD service name of PIXLA client
	 */
	private const SERVICE_NAME = 'gwman-client.service';

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 */
	public function __construct(CommandManager $commandManager) {
		$this->commandManager = $commandManager;
	}

	/**
	 * Disables and stops PIXLA client service
	 */
	public function disableService(): void {
		$commands = [
			'systemctl stop ' . self::SERVICE_NAME,
			'systemctl disable ' . self::SERVICE_NAME,
		];
		foreach ($commands as $command) {
			$this->commandManager->run($command, true);
		}
	}

	/**
	 * Enables and starts PIXLA client service
	 */
	public function enableService(): void {
		$commands = [
			'systemctl enable ' . self::SERVICE_NAME,
			'systemctl start ' . self::SERVICE_NAME,
		];
		foreach ($commands as $command) {
			$this->commandManager->run($command, true);
		}
	}

	/**
	 * Returns PIXLA client service status
	 */
	public function getServiceStatus(): PixlaService {
		$command = 'systemctl is-enabled ' . self::SERVICE_NAME;
		$status = $this->commandManager->run($command, true);
		return PixlaService::fromScalar($status);
	}

	/**
	 * Returns PIXLA token
	 * @return string|null PIXLA token
	 */
	public function getToken(): ?string {
		$token = $this->commandManager->run('cat /etc/gwman/customer_id', true);
		if ($token !== '') {
			return $token;
		}
		return null;
	}

}
