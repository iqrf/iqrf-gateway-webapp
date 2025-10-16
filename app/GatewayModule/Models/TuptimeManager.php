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

namespace App\GatewayModule\Models;

use App\GatewayModule\Entities\GatewayUptime;
use App\GatewayModule\Exceptions\TuptimeErrorException;
use App\GatewayModule\Exceptions\TuptimeNotFoundException;
use Iqrf\CommandExecutor\CommandExecutor;
use League\Csv\Exception;
use League\Csv\Reader;

/**
 * Gateway uptime stats manager
 */
class TuptimeManager {

	/**
	 * Constructor
	 * @param CommandExecutor $commandManager Command manager
	 */
	public function __construct(
		private readonly CommandExecutor $commandManager,
	) {
	}

	/**
	 * Lists gateway uptimes
	 * @return array<GatewayUptime> List of gateway uptimes
	 * @thrown TuptimeNotFoundException tuptime not found
	 * @thrown TuptimeErrorException tuptime error
	 */
	public function list(): array {
		if (!$this->commandManager->commandExist('tuptime')) {
			throw new TuptimeNotFoundException();
		}
		$command = $this->commandManager->run('tuptime -ckpst');
		try {
			$csv = Reader::fromString($command->getStdout());
			$csv->setHeaderOffset(0);
			$data = [];
			foreach ($csv->getRecords() as $record) {
				$data[] = GatewayUptime::fromTuptime($record);
			}
			return $data;
		} catch (Exception $e) {
			throw new TuptimeErrorException(message: 'CSV parsing error', previous: $e);
		}
	}

}
