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

/**
 * Interface for backup managers
 */
interface IBackupManager {

	/**
	 * Temporary backup directory
	 */
	public const TMP_PATH = '/tmp/backup/';

	/**
	 * Performs backup
	 * @param array<string, array<string, bool>> $params Request parameters
	 * @param array<string, bool> $services Array of services
	 */
	public function backup(array $params, array &$services): void;

	/**
	 * Performs restore
	 */
	public function restore(): void;

}
