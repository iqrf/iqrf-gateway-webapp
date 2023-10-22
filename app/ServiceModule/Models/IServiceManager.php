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
use App\ServiceModule\Exceptions\NonexistentServiceException;

/**
 * Interface for tools for managing service
 */
interface IServiceManager {

	/**
	 * Disables the service
	 * @param string $serviceName Service name
	 * @throws NonexistentServiceException
	 */
	public function disable(string $serviceName): void;

	/**
	 * Enables the service
	 * @param string $serviceName Service name
	 * @throws NonexistentServiceException
	 */
	public function enable(string $serviceName): void;

	/**
	 * Checks if the service is active
	 * @param string $serviceName Service name
	 * @return bool Is service active?
	 * @throws NonexistentServiceException
	 */
	public function isActive(string $serviceName): bool;

	/**
	 * Checks if the service is enabled
	 * @param string $serviceName Service name
	 * @return bool Is service enabled?
	 * @throws NonexistentServiceException
	 */
	public function isEnabled(string $serviceName): bool;

	/**
	 * Starts the service
	 * @param string $serviceName Service name
	 * @throws NonexistentServiceException
	 */
	public function start(string $serviceName): void;

	/**
	 * Stops the service
	 * @param string $serviceName Service name
	 * @throws NonexistentServiceException
	 */
	public function stop(string $serviceName): void;

	/**
	 * Restarts the service
	 * @param string $serviceName Service name
	 * @throws NonexistentServiceException
	 */
	public function restart(string $serviceName): void;

	/**
	 * Returns status of the service
	 * @param string $serviceName Service name
	 * @return string Service status
	 * @throws NonexistentServiceException
	 */
	public function getStatus(string $serviceName): string;

	/**
	 * Returns state of the service
	 * @param string $serviceName Service name
	 * @param bool $withStatus Include service status?
	 * @return ServiceState Service state
	 * @throws NonexistentServiceException
	 */
	public function getState(string $serviceName, bool $withStatus = false): ServiceState;

}
