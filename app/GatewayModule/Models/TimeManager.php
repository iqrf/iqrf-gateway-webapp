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

namespace App\GatewayModule\Models;

use App\ConfigModule\Utils\ConfParser;
use App\CoreModule\Models\CommandManager;
use App\GatewayModule\Exceptions\NonexistentTimezoneException;
use App\GatewayModule\Exceptions\TimeDateException;
use DateTime;
use DateTimeZone;
use Throwable;

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
	 * Retrieves current time and timezone
	 * @return array<string, array<string, string>> Time and timezone
	 */
	public function currentTime(): array {
		$timestamp = $this->getTimestamp();
		$status = $this->getStatus();
		$timezone = $this->timezoneInfo($status['Timezone']);
		return array_merge(['timestamp' => $timestamp, 'ntpSynchronized' => $status['NTPSynchronized']], $timezone);
	}

	/**
	 * Returns the current timestamp
	 * @return int Timestamp
	 */
	public function getTimestamp(): int {
		$command = $this->commandManager->run('date +%s');
		if ($command->getExitCode() !== 0) {
			throw new TimeDateException($command->getStderr());
		}
		return intval($command->getStdout());
	}

	/**
	 * Returns timedatectl status
	 * @return array<string, mixed|array<string, mixed>> Timedatectl status
	 */
	public function getStatus(): array {
		$command = $this->commandManager->run('timedatectl show');
		if ($command->getExitCode() !== 0) {
			throw new TimeDateException($command->getStderr());
		}
		return ConfParser::toArray($command->getStdout());
	}

	/**
	 * Retrieves an array of available timezones
	 * @return array<int, array<string, string>> Array of available timezones
	 */
	public function availableTimezones(): array {
		$command = $this->commandManager->run('timedatectl list-timezones');
		$timezones = explode(PHP_EOL, $command->getStdout());
		$array = [];
		foreach ($timezones as $timezone) {
			try {
				$array[] = $this->timezoneInfo($timezone);
			} catch (Throwable $e) {
				continue;
			}
		}
		return $array;
	}

	/**
	 * Retrieves timezone information
	 * @param string $timezone Timezone name
	 * @return array<string, string> Timezone name, abbreviation and offset
	 */
	public function timezoneInfo(string $timezone): array {
		$time = new DateTime('now', new DateTimeZone($timezone));
		$timezoneInfo = explode(' ', $time->format('T O'));
		return [
			'name' => $timezone,
			'code' => $timezoneInfo[0],
			'offset' => $timezoneInfo[1],
		];
	}

	/**
	 * Sets new gateway timezone
	 * @param string $timezone Timezone name
	 */
	public function setTimezone(string $timezone): void {
		$command = $this->commandManager->run('timedatectl set-timezone ' . $timezone, true);
		if ($command->getExitCode() !== 0) {
			throw new NonexistentTimezoneException($command->getStderr());
		}
	}

}
