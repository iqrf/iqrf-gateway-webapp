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

namespace App\NetworkModule\Models;

use App\CoreModule\Entities\ICommand;
use App\CoreModule\Models\CommandManager;
use App\NetworkModule\Entities\Modem;
use App\NetworkModule\Exceptions\ModemManagerException;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use stdClass;

/**
 * Cellular manager
 */
class CellularManager {

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 */
	public function __construct(
		private readonly CommandManager $commandManager,
	) {
	}

	/**
	 * Scans for cellular modems
	 */
	public function scanModems(): void {
		$output = $this->commandManager->run('mmcli --scan-modems', true);
		$this->checkCommand($output);
	}

	/**
	 * Lists available modems
	 * @return array<array{interface: string, signal: int, rssi: float}> Available modems
	 */
	public function listModems(): array {
		$output = $this->commandManager->run('mmcli --list-modems --output-json', true);
		$this->checkCommand($output);
		try {
			$json = Json::decode($output->getStdout(), forceArrays: true);
			$entities = array_map(fn (string $path): array => $this->getModemInformation($path)->jsonSerialize(), $json['modem-list']);
		} catch (JsonException $e) {
			throw new ModemManagerException($e->getMessage());
		}
		return $entities;
	}

	/**
	 * Retrieves modem information and returns entity
	 * @param string $path Modem path
	 * @return Modem Modem entity
	 */
	private function getModemInformation(string $path): Modem {
		$command = sprintf('mmcli -m %s --output-json', $path);
		$output = $this->commandManager->run($command, true);
		$this->checkCommand($output);
		$modem = Json::decode($output->getStdout());
		$rssi = null;
		$command = sprintf('mmcli -m %s --signal-get --output-json', $path);
		$output = $this->commandManager->run($command, true);
		if ($output->getExitCode() === 0) {
			$rssi = $this->getModemRssi($path);
		}
		return Modem::fromMmcliJson($modem, $rssi);
	}

	/**
	 * Retrieves modem RSSI
	 * @param string $path Modem path
	 * @return stdClass|null Modem RSSI
	 */
	private function getModemRssi(string $path): ?stdClass {
		$command = sprintf('mmcli -m %s --signal-setup=300', $path);
		$output = $this->commandManager->run($command, true);
		if ($output->getExitCode() !== 0) {
			return null;
		}
		$command = sprintf('mmcli -m %s --signal-get --output-json', $path);
		$output = $this->commandManager->run($command, true);
		return $output->getExitCode() === 0 ? Json::decode($output->getStdout()) : null;
	}

	private function checkCommand(ICommand $command): void {
		if ($command->getExitCode() !== 0) {
			throw new ModemManagerException($command->getStderr());
		}
	}

}
