<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2018 IQRF Tech s.r.o.
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
 * Interface for tools for managing IQRF Gateway Daemon's service
 */
interface IServiceManager {

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 */
	public function __construct(CommandManager $commandManager);

	/**
	 * Start IQRF Gateway Daemon's service
	 * @return string Output from init daemon
	 */
	public function start(): string;

	/**
	 * Stop IQRF Gateway Daemon's service
	 * @return string Output from init daemon
	 */
	public function stop(): string;

	/**
	 * Restart IQRF Gateway Daemon's service
	 * @return string Output from init daemon
	 */
	public function restart(): string;

	/**
	 * Get status of IQRF Gateway Daemon's service
	 * @return string Output from init daemon
	 */
	public function getStatus(): string;
}
