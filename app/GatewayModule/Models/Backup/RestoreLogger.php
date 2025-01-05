<?php

/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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

namespace App\GatewayModule\Models\Backup;

use Contributte\Monolog\LoggerManager;

/**
 * Logger wrapper for backup and restore process
 */
class RestoreLogger {

	/**
	 * @var LoggerManager $loggerManager Logger manager
	 */
	private LoggerManager $loggerManager;

	/**
	 * @param LoggerManager $loggerManager Logger manager
	 */
	public function __construct(LoggerManager $loggerManager) {
		$this->loggerManager = $loggerManager;
	}

	/**
	 * Logs a message
	 * @param string $message Message to log
	 */
	public function log(string $message): void {
		$this->loggerManager->get('restore')->notice($message);
	}

}
