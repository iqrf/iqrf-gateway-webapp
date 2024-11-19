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

use App\GatewayModule\Exceptions\UnsupportedPackageManagerException;
use Iqrf\CommandExecutor\CommandExecutor;
use Nette\Utils\Strings;

/**
 * Adapter for apt-get package manager
 */
class AptGetPackageManager implements IPackageManager {

	/**
	 * Constructor
	 * @param CommandExecutor $commandManager Command manager
	 */
	public function __construct(
		private readonly CommandExecutor $commandManager,
	) {
		if (!$this->commandManager->commandExist('apt-get')) {
			throw new UnsupportedPackageManagerException();
		}
	}

	/**
	 * Installs the packages
	 * @param callable $callback Callback
	 * @param array<string> $packages Packages to install
	 */
	public function install(callable $callback, array $packages): void {
		$command = 'apt-get install -y ' . $this->formatPackages($packages);
		$this->commandManager->runAsync($callback, $command, true);
	}

	/**
	 * Lists upgradable packages
	 * @param callable $callback Callback
	 */
	public function listUpgradable(callable $callback): void {
		$this->commandManager->runAsync($callback, 'apt-get -s upgrade -V', true);
	}

	/**
	 * Returns list of upgradable packages
	 * @return array<array{name: string, oldVersion: string, newVersion: string}> Upgradable packages
	 */
	public function getUpgradable(): array {
		$stdout = $this->commandManager->run('apt-get -s upgrade -V', true)->getStdout();
		return array_map(static fn (array $result): array => [
			'name' => $result['name'],
			'oldVersion' => $result['oldVersion'],
			'newVersion' => $result['newVersion'],
		], Strings::matchAll($stdout, '#^\s*\K(?P<name>.*) \((?P<oldVersion>.*) => (?P<newVersion>.*)\)$#m'));
	}

	/**
	 * Purges the packages
	 * @param callable $callback Callback
	 * @param array<string> $packages Packages to purge
	 */
	public function purge(callable $callback, array $packages): void {
		if ($packages === []) {
			return;
		}
		$command = 'apt-get purge -y ' . $this->formatPackages($packages);
		$this->commandManager->runAsync($callback, $command, true);
	}

	/**
	 * Removes the packages
	 * @param callable $callback Callback
	 * @param array<string> $packages Packages to remove
	 */
	public function remove(callable $callback, array $packages): void {
		if ($packages === []) {
			return;
		}
		$command = 'apt-get remove -y ' . $this->formatPackages($packages);
		$this->commandManager->runAsync($callback, $command, true);
	}

	/**
	 * Updates a list of packages
	 * @param callable $callback Callback
	 */
	public function update(callable $callback): void {
		$this->commandManager->runAsync($callback, 'apt-get update', true);
	}

	/**
	 * Upgrades packages
	 * @param callable $callback Callback
	 */
	public function upgrade(callable $callback): void {
		$this->commandManager->runAsync($callback, 'apt-get upgrade -y', true);
	}

	/**
	 * Formats array of packages to string
	 * @param array<string> $packages Array of packages
	 * @return string List of packages
	 */
	private function formatPackages(array $packages): string {
		return implode(' ', array_map(static fn (string $package): string => escapeshellarg($package), $packages));
	}

}
