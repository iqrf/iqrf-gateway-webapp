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

use App\CoreModule\Models\ZipArchiveManager;

/**
 * Gateway file backup manager
 */
class GatewayFileBackup implements IBackupManager {

	/**
	 * Path to configuration directory
	 */
	private const CONF_PATH = '/etc/';

	/**
	 * Gateway files whitelist
	 */
	public const WHITELIST = [
		'iqrf-gateway.json',
	];

	/**
	 * @var string Gateway ID
	 */
	private $gwId;

	/**
	 * @var string Gateway token
	 */
	private $gwToken;

	/**
	 * @var ZipArchiveManager ZIP archive manager
	 */
	private $zipManager;

	/**
	 * Constructor
	 * @param string $gwId Gateway ID
	 * @param string $gwToken Gateway token
	 * @param ZipArchiveManager $zipManager ZIP archive manager
	 */
	public function __construct(string $gwId, string $gwToken, ZipArchiveManager $zipManager) {
		$this->gwId = $gwId;
		$this->gwToken = $gwToken;
		$this->zipManager = $zipManager;
	}

	/**
	 * Performs gateway file backup
	 * @param array<string, array<string, bool>> $params Request parameters
	 */
	public function backup(array $params): void {
		$this->gwId = $this->gwId;
		$this->gwToken = $this->gwToken;
		if (!$params['software']['iqrf']) {
			return;
		}
		$this->zipManager->addFile(self::CONF_PATH . 'iqrf-gateway.json', 'gateway/iqrf-gateway.json');
	}

	/**
	 * Performs gateway file restore
	 */
	public function restore(): void {
		if (!$this->zipManager->exist('gateway/')) {
			return;
		}
	}

}
