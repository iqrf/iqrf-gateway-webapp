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

use App\GatewayModule\Exceptions\JournalReaderArgumentException;
use App\GatewayModule\Exceptions\JournalReaderInternalException;
use Iqrf\CommandExecutor\CommandExecutor;
use Nette\Utils\Json;

class JournalReaderManager {

	/**
	 * Journal reader utility command
	 */
	private const READER = 'iqrf-journal-reader';

	/**
	 * Constructor
	 * @param CommandExecutor $commandManager Command manager
	 */
	public function __construct(
		private readonly CommandExecutor $commandManager,
	) {
	}

	/**
	 * Gets number of journal records from end of journal or specified cursor
	 * @param int $count Number of records to get
	 * @param string|null $cursor Journal cursor
	 * @return array<string, string|array<int, string>> Journal records and cursors
	 */
	public function getRecords(int $count, ?string $cursor = null): array {
		if (!$this->commandManager->commandExist(self::READER)) {
			throw new JournalReaderInternalException('IQRF Journal Reader is not installed.');
		}
		$command = sprintf('%s -j -n %d', self::READER, $count);
		if ($cursor !== null) {
			$command = sprintf('%s -e %s', $command, escapeshellarg($cursor));
		}
		$result = $this->commandManager->run($command, true);
		if ($result->getExitCode() === 1) {
			throw new JournalReaderArgumentException($result->getStderr());
		}
		if ($result->getExitCode() === 2) {
			throw new JournalReaderInternalException($result->getStderr());
		}
		$output = Json::decode($result->getStdout(), forceArrays: true);
		$records = [];
		foreach ($output['records'] as $record) {
			$records[] = sprintf('%s %s %s[%d]: %s', $record['timestamp'], $record['hostname'], $record['identifier'], $record['pid'], $record['message']);
		}
		return [
			'records' => $records,
			'startCursor' => $output['firstCursor'],
			'endCursor' => $output['lastCursor'],
		];
	}

}
