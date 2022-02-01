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

namespace App\GatewayModule\Models\Backup;

use App\CoreModule\Models\CommandManager;
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
	public const WHITELIST = [
		'timezone',
	];

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * @var TimeManager Time manager
	 */
	private $timeManager;

	/**
	 * @var ZipArchiveManager ZIP archive manager
	 */
	private $zipManager;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function __construct(CommandManager $commandManager, ZipArchiveManager $zipManager) {
		$this->commandManager = $commandManager;
		$this->timeManager = new TimeManager($this->commandManager);
		$this->zipManager = $zipManager;
	}

	/**
	 * Performs time backup
	 * @param array<string, array<string, bool>> $params Request parameters
	 * @param array<string, bool> $services Array of services
	 */
	public function backup(array $params, array &$services): void {
		if (!$params['system']['time']) {
			return;
		}
		$timezone = $this->timeManager->getStatus()['Timezone'];
		$this->zipManager->addFileFromText('time/timezone', $timezone);
	}

	/**
	 * Performs time restore
	 */
	public function restore(): void {
		if (!$this->zipManager->exist('time/')) {
			return;
		}
		$this->zipManager->extract(self::TMP_PATH, 'time/timezone');
		$this->timeManager->setTimezone(FileSystem::read(self::TMP_PATH . 'time/timezone'));
		$this->commandManager->run('rm -rf ' . self::TMP_PATH . 'time');
	}

}
