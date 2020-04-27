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

use App\ServiceModule\Exceptions\UnsupportedInitSystemException;
use Nette\SmartObject;

/**
 * Tool for managing services (unknown init daemon)
 */
class UnknownManager implements IServiceManager {

	use SmartObject;

	/**
	 * Starts IQRF Gateway Daemon
	 * @throws UnsupportedInitSystemException
	 */
	public function start(): void {
		throw new UnsupportedInitSystemException();
	}

	/**
	 * Stops the service
	 * @throws UnsupportedInitSystemException
	 */
	public function stop(): void {
		throw new UnsupportedInitSystemException();
	}

	/**
	 * Restarts the service
	 * @throws UnsupportedInitSystemException
	 */
	public function restart(): void {
		throw new UnsupportedInitSystemException();
	}

	/**
	 * Gets status of the service
	 * @throws UnsupportedInitSystemException
	 */
	public function getStatus(): string {
		throw new UnsupportedInitSystemException();
	}

}
