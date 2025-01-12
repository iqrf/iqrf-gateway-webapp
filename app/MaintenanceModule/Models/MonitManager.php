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

namespace App\MaintenanceModule\Models;

use App\MaintenanceModule\Exceptions\MonitConfigErrorException;
use App\MaintenanceModule\Models\Monit\BaseMonitManager;
use App\MaintenanceModule\Models\Monit\CheckManager;
use App\MaintenanceModule\Models\Monit\MmonitManager;
use Iqrf\CommandExecutor\CommandExecutor;
use Iqrf\FileManager\IFileManager;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;
use Nette\Schema\Processor;

/**
 * Monit manager
 */
class MonitManager extends BaseMonitManager {

	/**
	 * @var CheckManager Monit check manager
	 */
	private readonly CheckManager $checkManager;

	/**
	 * @var MmonitManager M/Monit configuration manager
	 */
	private readonly MmonitManager $mmonitManager;

	/**
	 * Constructor
	 * @param IFileManager $fileManager Privileged file manager
	 * @param CommandExecutor $commandManager Command manager
	 */
	public function __construct(
		IFileManager $fileManager,
		CommandExecutor $commandManager,
	) {
		parent::__construct($fileManager, $commandManager);
		$this->checkManager = new CheckManager($fileManager, $commandManager);
		$this->mmonitManager = new MmonitManager($fileManager, $commandManager);
	}

	/**
	 * Disables Monit check
	 * @param string $name Monit check name to disable
	 */
	public function disableCheck(string $name): void {
		$this->checkManager->setStates([['name' => $name, 'enabled' => false]]);
	}

	/**
	 * Enables Monit check
	 * @param string $name Monit check name to enable
	 */
	public function enableCheck(string $name): void {
		$this->checkManager->setStates([['name' => $name, 'enabled' => true]]);
	}

	/**
	 * Returns Monit check configuration
	 * @param string $name Monit check name
	 * @return array{name: string, enabled: bool} Monit check configuration
	 */
	public function getCheck(string $name): array {
		return $this->checkManager->get($name, true);
	}

	/**
	 * Parses configuration file and returns configuration as array
	 * @return array{checks: array<array{name: string, enabled: bool}>, mmonit: array{enabled: bool, credentials: array{username: string, password: string}, server: string}} Monit configuration array
	 * @throws MonitConfigErrorException
	 */
	public function getConfig(): array {
		$config = [
			'checks' => $this->checkManager->list(),
			'mmonit' => $this->mmonitManager->readConfig(),
		];
		$processor = new Processor();
		return $processor->process($this->getSchema(), $config);
	}

	/**
	 * Saves new monit configuration
	 * @param array{
	 *     checks: array<array{
	 *         name: string,
	 *         enabled: bool,
	 *     }>,
	 *     mmonit: array{
	 *         enabled: bool,
	 *         credentials: array{
	 *             username: string,
	 *             password: string,
	 *         },
	 *         server: string,
	 *     },
	 * } $newConfig New monit configuration
	 * @throws MonitConfigErrorException
	 */
	public function saveConfig(array $newConfig): void {
		$this->checkManager->setStates($newConfig['checks']);
		$this->mmonitManager->writeConfig($newConfig['mmonit']);
	}

	/**
	 * Returns configuration file schema
	 * @return Structure Configuration file schema
	 */
	private function getSchema(): Structure {
		return Expect::structure([
			'checks' => $this->checkManager->getEnablementSchema()->default([]),
			'mmonit' => $this->mmonitManager->getSchema(),
		])->castTo('array');
	}

}
