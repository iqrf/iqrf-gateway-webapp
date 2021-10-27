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

namespace App\NetworkModule\Models;

use App\CoreModule\Models\CommandManager;
use App\NetworkModule\Entities\Modem;
use App\NetworkModule\Exceptions\ModemManagerException;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

/**
 * GSM manager
 */
class GsmManager {

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
	 * Lists available modems
	 * @return array<Modem> Available modems
	 */
	public function listModems(): array {
		$output = $this->commandManager->run('mmcli --list-modems --output-json', true);
		if ($output->getExitCode() !== 0) {
			throw new ModemManagerException($output->getStderr());
		}
		$entities = [];
		$json = [];
		try {
			$json = Json::decode($output->getStdout(), Json::FORCE_ARRAY);
		} catch (JsonException $e) {
			throw new ModemManagerException($e->getMessage());
		}
		foreach ($json['modem-list'] as $entry) {
			$entities[] = $this->getModemInformation($entry);
		}
		return $entities;
	}

	/**
	 * Retrieves modem information and returns entity
	 * @param string $path Modem path
	 * @return Modem Modem entity
	 */
	private function getModemInformation(string $path): Modem {
		$command = sprintf('mmcli -m %s --output-json', true);
		$output = $this->commandManager->run($command);
		if ($output->getExitCode() !== 0) {
			throw new ModemManagerException($output->getStderr());
		}
		try {
			$json = Json::decode($output->getStdout());
			return Modem::fromMmcliJson($json);
		} catch (JsonException $e) {
			throw new ModemManagerException($e->getMessage());
		}
	}

}
