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

use App\CoreModule\Models\ZipArchiveManager;
use App\GatewayModule\Models\TimeManager;
use Nette\Utils\FileSystem;

/**
 * Time backup manager
 */
class TimeBackup implements IBackupManager {

	/**
	 * Whitelisted files
	 */
	final public const WHITELIST = [
		'timezone',
	];

	/**
	 * Constructor
	 * @param RestoreLogger $restoreLogger Restore logger
	 * @param TimeManager $timeManager Time manager
	 */
	public function __construct(
		private readonly RestoreLogger $restoreLogger,
		private readonly TimeManager $timeManager,
	) {
	}

	/**
	 * Performs time backup
	 * @param array<string, array<string, bool>> $params Request parameters
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function backup(array $params, ZipArchiveManager $zipManager): void {
		if (!$params['system']['time']) {
			return;
		}
		$timezone = $this->timeManager->getStatus()['Timezone'];
		$zipManager->addFileFromText('time/timezone', $timezone);
	}

	/**
	 * Performs time restore
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function restore(ZipArchiveManager $zipManager): void {
		if (!$zipManager->exist('time/')) {
			return;
		}
		$this->restoreLogger->log('Restoring gateway timezone.');
		$zipManager->extract(self::TMP_PATH, 'time/timezone');
		$this->timeManager->setTimezone(FileSystem::read(self::TMP_PATH . 'time/timezone'));
		FileSystem::delete(self::TMP_PATH . 'time');
	}

	/**
	 * Returns service names
	 * @return array<string> Service names
	 */
	public function getServices(): array {
		return [];
	}

}
