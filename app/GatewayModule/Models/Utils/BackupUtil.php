<?php

/**
 * Copyright 2017-2022 IQRF Tech s.r.o.
 * Copyright 2019-2022 MICRORISC s.r.o.
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

namespace App\GatewayModule\Models\Utils;

use App\CoreModule\Entities\CommandStack;
use App\CoreModule\Models\CommandManager;

class BackupUtil {

	/**
	 * Returns user and group string of current process
	 * @param array<int, string> $dirs Array of directory paths
	 */
	public static function recreateDirectories(array $dirs): void {
		$user = posix_getpwuid(posix_geteuid());
		$owner = $user['name'] . ':' . posix_getgrgid($user['gid'])['name'];
		$commandStack = new CommandStack();
		$commandManager = new CommandManager(true, $commandStack);
		foreach ($dirs as $dir) {
			$commandManager->run('rm -rf ' . $dir, true);
			$commandManager->run('mkdir ' . $dir, true);
			$commandManager->run('chown ' . $owner . ' ' . $dir, true);
			$commandManager->run('chown -R ' . $owner . ' ' . $dir, true);
		}
	}

}
