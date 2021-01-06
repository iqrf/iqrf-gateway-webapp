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

namespace App\GatewayModule\Models;

use App\CoreModule\Models\CommandManager;

/**
 * Tool for troubleshooting gateway
 */
class TroubleshootManager {

	/**
	 * IQRF GW Controller command
	 */
	private const CONTROLLER = 'iqrf-gateway-controller';

	/**
	 * IQRF GW Daemon command
	 */
	private const DAEMON = 'iqrf-gateway-daemon';

	/**
	 * IQRF GW Translator command
	 */
	private const TRANSLATOR = 'iqrf-gateway-translator';

	/**
	 * Mender client command
	 */
	private const MENDER = 'mender-client';

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
	 * Runs troubleshoot of services and features
	 * @return Array<string, mixed> Troubleshoot results
	 */
	public function troubleshoot(): array {
		$array = [];
		$array['features'] = [
			'controller' => $this->checkService(self::CONTROLLER, '/etc/' . self::CONTROLLER . '/config.json'),
			'translator' => $this->checkService(self::TRANSLATOR, '/etc/' . self::TRANSLATOR . '/config.json'),
			'mender' => $this->checkService(self::MENDER, '/etc/mender/mender.conf'),
		];
		return $array;
	}

	/**
	 * Checks if Controller is installed, configuration exists and permission set.
	 * @param string $service Name of service
	 * @param string $file Name of file
	 * @return Array<string, bool|int> Controller service metadata
	 */
	private function checkService(string $service, string $file): array {
		$array = [
			'installed' => false,
			'config' => false,
			'permission' => false,
		];
		if ($this->commandManager->commandExist($service)) {
			$array['installed'] = true;
		}
		if (file_exists($file)) {
			$array['config'] = true;
		}
		$array['permission'] = $this->checkPermission($file);
		return $array;
	}

	/**
	 * Checks permission set for a specified file
	 * @param string $file Path to file
	 * @return int Permission code or -1
	 */
	private function checkPermission(string $file): int {
		$command = $this->commandManager->run('stat -c "%a" ' . $file, false);
		if ($command->getExitCode() === 0) {
			return intval($command->getStdout());
		}
		return -1;
	}

}
