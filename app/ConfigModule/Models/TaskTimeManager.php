<?php

/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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

namespace App\ConfigModule\Models;

use Nette\Utils\Strings;
use stdClass;

/**
 * Scheduler's task time specification manager
 */
class TaskTimeManager {

	/**
	 * @var array<string> CRON aliases
	 */
	private array $aliases = ['@reboot', '@yearly', '@annually', '@monthly', '@weekly', '@daily', '@hourly'];

	/**
	 * Converts a cron time from a string to an array
	 * @param stdClass $config Tasks's configuration
	 */
	public function cronToArray(stdClass &$config): void {
		$cron = &$config->timeSpec->cronTime;
		if (is_array($cron)) {
			return;
		}
		$cron = Strings::replace(Strings::trim($cron), '#\?#', '*');
		if (in_array($cron, $this->aliases, true)) {
			$cron = [$cron, '', '', '', '', '', ''];
			return;
		}
		$cron = explode(' ', $cron);
		switch (count($cron)) {
			case 5:
				array_unshift($cron, '0');
				$cron[] = '*';
				break;
			case 6:
				if (strlen($cron[5]) === 4) {
					array_unshift($cron, '0');
				} else {
					$cron[] = '*';
				}
				break;
			case 7:
				break;
			default:
				$cron = ['', '', '', '', '', '', ''];
		}
	}

	/**
	 * Converts a cron time from an array to a string
	 * @param stdClass $config Task's configuration
	 */
	public function cronToString(stdClass &$config): void {
		$cron = &$config->timeSpec->cronTime;
		$cron = Strings::trim(implode(' ', $cron));
	}

}
