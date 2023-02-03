<?php

/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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

/**
 * Adapter for unsupported package manager
 */
class UnsupportedPackageManager implements IPackageManager {

	/**
	 * Installs the packages
	 * @param callable $callback Callback
	 * @param array<string> $packages Packages to install
	 * @throws UnsupportedPackageManagerException
	 */
	public function install(callable $callback, array $packages): void {
		throw new UnsupportedPackageManagerException();
	}

	/**
	 * Lists upgradable packages
	 * @param callable $callback Callback
	 * @throws UnsupportedPackageManagerException
	 */
	public function listUpgradable(callable $callback): void {
		throw new UnsupportedPackageManagerException();
	}

	/**
	 * Returns list of upgradable packages
	 * @throws UnsupportedPackageManagerException
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingTraversableReturnTypeHintSpecification
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingTraversableTypeHintSpecification
	 */
	public function getUpgradable(): array {
		throw new UnsupportedPackageManagerException();
	}

	/**
	 * Purges the packages
	 * @param callable $callback Callback
	 * @param array<string> $packages Packages to purge
	 * @throws UnsupportedPackageManagerException
	 */
	public function purge(callable $callback, array $packages): void {
		throw new UnsupportedPackageManagerException();
	}

	/**
	 * Removes the packages
	 * @param callable $callback Callback
	 * @param array<string> $packages Packages to remove
	 * @throws UnsupportedPackageManagerException
	 */
	public function remove(callable $callback, array $packages): void {
		throw new UnsupportedPackageManagerException();
	}

	/**
	 * Updates a list of packages
	 * @param callable $callback Callback
	 * @throws UnsupportedPackageManagerException
	 */
	public function update(callable $callback): void {
		throw new UnsupportedPackageManagerException();
	}

	/**
	 * Upgrades packages
	 * @param callable $callback Callback
	 * @throws UnsupportedPackageManagerException
	 */
	public function upgrade(callable $callback): void {
		throw new UnsupportedPackageManagerException();
	}

}
