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
use App\CoreModule\Models\JsonFileManager;
use Nette\IOException;
use Nette\Utils\JsonException;

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
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManager;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 */
	public function __construct(JsonFileManager $fileManager, CommandManager $commandManager) {
		$this->commandManager = $commandManager;
		$this->fileManager = $fileManager;
	}

	/**
	 * Runs troubleshoot of services and features
	 * @return array<string, mixed> Troubleshoot results
	 */
	public function troubleshoot(): array {
		$array = [];
		$array['daemon'] = [
			'interfaces' => $this->getEnabledInterfaces(),
		];
		$array['features'] = [];
		array_push($array['features'], array_merge(['name' => 'controller'], $this->getService(self::CONTROLLER, '/etc/' . self::CONTROLLER . '/config.json')));
		array_push($array['features'], array_merge(['name' => 'translator'], $this->getService(self::TRANSLATOR, '/etc/' . self::TRANSLATOR . '/config.json')));
		array_push($array['features'], array_merge(['name' => 'mender'], $this->getService(self::MENDER, '/etc/mender/mender.conf')));
		return $array;
	}

	/**
	 * Retrieves IQRF interfaces enabled in Daemon configuration
	 * @return array<string> Enabled IQRF interfaces
	 */
	private function getEnabledInterfaces(): array {
		try {
			$components = $this->fileManager->read('config', true)['components'];
			$interfaces = [];
			foreach ($components as $component) {
				if ($component['name'] !== 'iqrf::IqrfCdc' &&
					$component['name'] !== 'iqrf::IqrfSpi' &&
					$component['name'] !== 'iqrf::IqrfUart') {
					continue;
				}
				if ($component['enabled']) {
					array_push($interfaces, $component['name']);
				}
			}
			return $interfaces;
		} catch (IOException | JsonException $e) {
			return ['error' => $e->getMessage()];
		}
	}

	/**
	 * Retrieves information about a gateway feature.
	 * @param string $service Name of service
	 * @param string $file Name of file
	 * @return array<string, bool|int> Controller service metadata
	 */
	private function getService(string $service, string $file): array {
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
	 * Retrieves permission code for a file
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
