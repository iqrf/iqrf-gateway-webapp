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

/**
 * Posix account helper
 */
class PosixHelper {

	/**
	 * Returns username and group name in format "username:groupname" to be used for chown
	 * @param int|null $userId Effective user ID to be used for chown
	 * @return string|null Username and group name in format "username:groupname" to be used for chown
	 * @link https://php.net/manual/en/function.posix-geteuid.php
	 */
	public static function getChownOwner(?int $userId = null): ?string {
		if ($userId === null) {
			$userId = posix_geteuid();
		}
		$userInfo = posix_getpwuid($userId);
		if ($userInfo === false) {
			return null;
		}
		$groupInfo = posix_getgrgid($userInfo['gid']);
		if ($groupInfo === false) {
			return null;
		}
		return escapeshellarg($userInfo['name'] . ':' . $groupInfo['name']);
	}

}
