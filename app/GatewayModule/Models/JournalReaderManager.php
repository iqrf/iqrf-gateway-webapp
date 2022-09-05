<?php

/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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

use App\CoreModule\Models\CommandManager;
use App\GatewayModule\Exceptions\JournalReaderException;
use Nette\Utils\Json;

class JournalReaderManager {

	/**
	 * Journal reader utility command
	 */
	private const READER = 'mini-journalreader';

	/**
	 * @var CommandManager $commandManager Command manager
	 */
	private CommandManager $commandManager;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 */
	public function __construct(CommandManager $commandManager) {
		$this->commandManager = $commandManager;
	}

	/**
	 * Gets number of journal records from end of journal or specified cursor
	 * @param int $count Number of records to get
	 * @param string|null $cursor Journal cursor
	 * @return array<string, string|array<int, string>> Journal records and cursors
	 */
	public function getRecords(int $count, ?string $cursor = null): array {
		$command = sprintf('%s -j -n %d', self::READER, $count);
		if ($cursor !== null) {
			$command = sprintf('%s -t "%s"', $command, $cursor);
		}
		$result = $this->commandManager->run($command, true);
		if ($result->getExitCode() !== 0) {
			throw new JournalReaderException($result->getStderr());
		}
		$output = Json::decode($result->getStdout(), Json::FORCE_ARRAY);
		$records = $output['data'];
		$startCursor = array_shift($records);
		$endCursor = array_pop($records);
		return [
			'records' => $records,
			'startCursor' => $startCursor,
			'endCursor' => $endCursor,
		];
	}

}
