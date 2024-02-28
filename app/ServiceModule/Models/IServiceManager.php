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

/**
 * Interface for tools for managing service
 */
interface IServiceManager {

	/**
	 * Disables the service
	 * @param string $serviceName Service name
	 * @param bool $stop Stop service after disabling
	 */
	public function disable(string $serviceName, bool $stop = true): void;

	/**
	 * Enables the service
	 * @param string $serviceName Service name
	 * @param bool $start Start service after enable
	 */
	public function enable(string $serviceName, bool $start = true): void;

	/**
	 * Checks if the service is active
	 * @param string $serviceName Service name
	 * @return bool Is service active?
	 */
	public function isActive(string $serviceName): bool;

	/**
	 * Checks if the service is enabled
	 * @param string $serviceName Service name
	 * @return bool Is service enabled?
	 */
	public function isEnabled(string $serviceName): bool;

	/**
	 * Starts the service
	 * @param string $serviceName Service name
	 */
	public function start(string $serviceName): void;

	/**
	 * Stops the service
	 * @param string $serviceName Service name
	 */
	public function stop(string $serviceName): void;

	/**
	 * Restarts the service
	 * @param string $serviceName Service name
	 */
	public function restart(string $serviceName): void;

	/**
	 * Returns status of the service
	 * @param string $serviceName Service name
	 * @return string Service status
	 */
	public function getStatus(string $serviceName): string;

}
