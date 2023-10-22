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

namespace App\ServiceModule\Models;

use App\ServiceModule\Entities\ServiceState;
use App\ServiceModule\Exceptions\UnsupportedInitSystemException;

/**
 * Tool for managing services (unknown init daemon)
 */
class UnknownManager implements IServiceManager {

	/**
	 * Disables the service
	 * @param string $serviceName Service name
	 * @throws UnsupportedInitSystemException
	 */
	public function disable(string $serviceName): void {
		throw new UnsupportedInitSystemException();
	}

	/**
	 * Enables the service
	 * @param string $serviceName Service name
	 * @throws UnsupportedInitSystemException
	 */
	public function enable(string $serviceName): void {
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
	 * Starts IQRF Gateway Daemon
	 * @param string $serviceName Service name
	 * @throws UnsupportedInitSystemException
	 */
	public function start(string $serviceName): void {
		throw new UnsupportedInitSystemException();
	}

	/**
	 * Stops the service
	 * @param string $serviceName Service name
	 * @throws UnsupportedInitSystemException
	 */
	public function stop(string $serviceName): void {
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

	/**
	 * Returns state of the service
	 * @param string $serviceName Service name
	 * @param bool $withStatus Include service status?
	 * @throws UnsupportedInitSystemException
	 */
	public function getState(string $serviceName, bool $withStatus = false): ServiceState {
		throw new UnsupportedInitSystemException();
	}

}
