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

use App\GatewayModule\Exceptions\UnsupportedPackageManagerException;
use App\GatewayModule\Models\PackageManagers\AptGetPackageManager;
use App\GatewayModule\Models\PackageManagers\IPackageManager;
use App\GatewayModule\Models\PackageManagers\UnsupportedPackageManager;
use Iqrf\CommandExecutor\CommandExecutor;

/**
 * Tool for updating packages of IQRF Gateways
 */
class UpdaterManager {

	/**
	 * @var IPackageManager Adapter for package manager
	 */
	private IPackageManager $packageManager;

	/**
	 * @var array<class-string<IPackageManager>> Adapters for package managers
	 */
	private array $packageManagers = [
		AptGetPackageManager::class,
	];

	/**
	 * Constructor
	 * @param CommandExecutor $commandManager Command manager
	 */
	public function __construct(CommandExecutor $commandManager) {
		foreach ($this->packageManagers as $packageManager) {
			try {
				$this->packageManager = new $packageManager($commandManager);
				break;
			} catch (UnsupportedPackageManagerException) {
				$this->packageManager = new UnsupportedPackageManager($commandManager);
			}
		}
	}

	/**
	 * Lists upgradable packages
	 * @param callable('out'|'err' $type, string $data): void $callback Callback
	 */
	public function listUpgradable(callable $callback): void {
		$this->packageManager->listUpgradable($callback);
	}

	/**
	 * Returns list of upgradable packages
	 * @return array<array{
	 *     name: string,
	 *     oldVersion: string,
	 *     newVersion: string,
	 * }> Upgradable packages
	 */
	public function getUpgradable(): array {
		return $this->packageManager->getUpgradable();
	}

	/**
	 * Updates a list of packages
	 * @param callable('out'|'err' $type, string $data): void $callback Callback
	 */
	public function update(callable $callback): void {
		$this->packageManager->update($callback);
	}

	/**
	 * Upgrades packages
	 * @param callable('out'|'err' $type, string $data): void $callback Callback
	 */
	public function upgrade(callable $callback): void {
		$this->packageManager->upgrade($callback);
	}

}
