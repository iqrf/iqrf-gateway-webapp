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

namespace App\ServiceModule\Models;

use App\CoreModule\Models\CommandManager;

/**
 * Interface for tools for managing service
 */
interface IServiceManager {

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 * @param string|null $serviceName Service name
	 */
	public function __construct(CommandManager $commandManager, ?string $serviceName = null);

	/**
	 * Starts the service
	 */
	public function start(): void;

	/**
	 * Stops the service
	 */
	public function stop(): void;

	/**
	 * Restarts the service
	 */
	public function restart(): void;

	/**
	 * Gets status of the service
	 * @return string Output from init daemon
	 */
	public function getStatus(): string;

}
