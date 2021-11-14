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
use App\GatewayModule\Exceptions\UnsupportedPackageManagerException;
use App\GatewayModule\Models\PackageManagers\AptGetPackageManager;
use App\GatewayModule\Models\PackageManagers\IPackageManager;
use App\GatewayModule\Models\PackageManagers\UnsupportedPackageManager;

/**
 * Tool for updating packages of IQRF Gateways
 */
class UpdaterManager {

	/**
	 * @var IPackageManager Adapter for package manager
	 */
	private $packageManager;

	/**
	 * @var array<string> Adapters for package managers
	 */
	private $packageManagers = [
		AptGetPackageManager::class,
	];

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 */
	public function __construct(CommandManager $commandManager) {
		foreach ($this->packageManagers as $packageManager) {
			try {
				$this->packageManager = new $packageManager($commandManager);
				break;
			} catch (UnsupportedPackageManagerException $e) {
				$this->packageManager = new UnsupportedPackageManager();
			}
		}
	}

	/**
	 * Lists upgradable packages
	 * @param callable $callback Callback
	 */
	public function listUpgradable(callable $callback): void {
		$this->packageManager->listUpgradable($callback);
	}

	/**
	 * Returns list of upgradable packages
	 * @return array<int, array<string, int|string>> Upgradable packages
	 */
	public function getUpgradable(): array {
		return $this->packageManager->getUpgradable();
	}

	/**
	 * Updates a list of packages
	 * @param callable $callback Callback
	 */
	public function update(callable $callback): void {
		$this->packageManager->update($callback);
	}

	/**
	 * Upgrades packages
	 * @param callable $callback Callback
	 */
	public function upgrade(callable $callback): void {
		$this->packageManager->upgrade($callback);
	}

}
