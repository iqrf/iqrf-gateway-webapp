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

namespace App\GatewayModule\Models\PackageManagers;

/**
 * Interface for package managers
 */
interface IPackageManager {

	/**
	 * Installs the packages
	 * @param callable $callback Callback
	 * @param array<string> $packages Packages to install
	 */
	public function install(callable $callback, array $packages): void;

	/**
	 * Lists upgradable packages
	 * @param callable $callback Callback
	 */
	public function listUpgradable(callable $callback): void;

	/**
	 * Returns list of upgradable packages
	 * @return array<array{name: string, oldVersion: string, newVersion: string}> Upgradable packages
	 */
	public function getUpgradable(): array;

	/**
	 * Purges the packages
	 * @param callable $callback Callback
	 * @param array<string> $packages Packages to purge
	 */
	public function purge(callable $callback, array $packages): void;

	/**
	 * Removes the packages
	 * @param callable $callback Callback
	 * @param array<string> $packages Packages to remove
	 */
	public function remove(callable $callback, array $packages): void;

	/**
	 * Updates a list of packages
	 * @param callable $callback Callback
	 */
	public function update(callable $callback): void;

	/**
	 * Upgrades packages
	 * @param callable $callback Callback
	 */
	public function upgrade(callable $callback): void;

}
