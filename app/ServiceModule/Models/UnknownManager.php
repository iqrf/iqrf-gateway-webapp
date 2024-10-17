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

namespace App\ServiceModule\Models;

use App\ServiceModule\Exceptions\UnsupportedInitSystemException;

/**
 * Tool for managing services (unknown init daemon)
 */
class UnknownManager implements IServiceManager {

	/**
	 * Disables a service
	 * @param string $service Service name
	 * @param bool $stop Stop service after disabling
	 * @throws UnsupportedInitSystemException
	 */
	public function disable(string $service, bool $stop = true): void {
		throw new UnsupportedInitSystemException();
	}

	/**
	 * Disables multiple services
	 * @param array<string> $services Service names
	 * @param bool $stop Stop services after disabling
	 * @throws UnsupportedInitSystemException
	 */
	public function disableMultiple(array $services, bool $stop = true): void {
		throw new UnsupportedInitSystemException();
	}

	/**
	 * Enables a service
	 * @param string $service Service name
	 * @param bool $start Start service after enable
	 * @throws UnsupportedInitSystemException
	 */
	public function enable(string $service, bool $start = true): void {
		throw new UnsupportedInitSystemException();
	}

	/**
	 * Enables multiple services
	 * @param array<string> $services Service names
	 * @param bool $start Start services after enable
	 * @throws UnsupportedInitSystemException
	 */
	public function enableMultiple(array $services, bool $start = true): void {
		throw new UnsupportedInitSystemException();
	}

	/**
	 * Checks if the service is active
	 * @param string $serviceName Service name
	 * @throws UnsupportedInitSystemException
	 */
	public function isActive(string $serviceName): bool {
		throw new UnsupportedInitSystemException();
	}

	/**
	 * Checks if the service is enabled
	 * @param string $serviceName Service name
	 * @throws UnsupportedInitSystemException
	 */
	public function isEnabled(string $serviceName): bool {
		throw new UnsupportedInitSystemException();
	}

	/**
	 * Starts a service
	 * @param string $service Service name
	 * @throws UnsupportedInitSystemException
	 */
	public function start(string $service): void {
		throw new UnsupportedInitSystemException();
	}

	/**
	 * Starts multiple services
	 * @param array<string> $services Service names
	 * @throws UnsupportedInitSystemException
	 */
	public function startMultiple(array $services): void {
		throw new UnsupportedInitSystemException();
	}

	/**
	 * Stops a service
	 * @param string $service Service name
	 * @throws UnsupportedInitSystemException
	 */
	public function stop(string $service): void {
		throw new UnsupportedInitSystemException();
	}

	/**
	 * Stops multiple services
	 * @param array<string> $services Service names
	 * @throws UnsupportedInitSystemException
	 */
	public function stopMultiple(array $services): void {
		throw new UnsupportedInitSystemException();
	}

	/**
	 * Restarts the service
	 * @param string $serviceName Service name
	 * @throws UnsupportedInitSystemException
	 */
	public function restart(string $serviceName): void {
		throw new UnsupportedInitSystemException();
	}

	/**
	 * Returns status of the service
	 * @param string $serviceName Service name
	 * @throws UnsupportedInitSystemException
	 */
	public function getStatus(string $serviceName): string {
		throw new UnsupportedInitSystemException();
	}

}
