<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2019 IQRF Tech s.r.o.
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
use DateTime;
use DateTimeZone;

/**
 * Time manager
 */
class TimeManager {

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 */
	public function __construct(CommandManager $commandManager) {
		$this->commandManager = $commandManager;
	}

	/**
	 * Retrieves current date, time and timezone
	 * @return string Date, time and timezone
	 */
	public function dateTime(): string {
		$dateTime = date('l d F Y, G:i:s');
		$timezone = date('e (T, O)');
		return ['dateTime' => $dateTime, 'timezone' => $timezone];
	}

	/**
	 * Retrieves an array of available timezones
	 * @return array<string> Array of available timezones
	 */
	public function availableTimezones(): array {
		$command = $this->commandManager->run('timedatectl list-timezones', false);
		$timezones = explode(PHP_EOL, $command->getStdout());
		for ($i = 0; $i < count($timezones); ++$i) {
			$timezones[$i] .= ' (' . $this->timezoneOffset($timezones[$i]) . ')';
		}
		return $timezones;
	}

	/**
	 * Retrieves timezone abbreviation and offset
	 * @param string $timezone Timezone name
	 * @return string Timezone abbreviation and offset
	 */
	private function timezoneOffset(string $timezone): string {
		$time = new DateTime('now', new DateTimeZone($timezone));
		return $time->format('T, O');
	}

	/**
	 * Sets specified timezone as system timezone
	 * @param string $timezone Timezone name
	 */
	public function setTimezone(string $timezone): void {
		$this->commandManager->run('timedatectl set-timezone' . $timezone, false);
	}

}
