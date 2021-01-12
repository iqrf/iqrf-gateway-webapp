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
use Symfony\Component\Process\Process;

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
		$array = [];
		$command = $this->commandManager->run('date +%s');
		$timestamp = [
			'timestamp' => $command->getStdout(),
			'sync' => $this->getNtp(),
		];
		$command = $this->commandManager->run('cat /etc/timezone');
		$timezone = $this->timezoneInfo($command->getStdout());
		$array['time'] = array_merge($timestamp, $timezone);
		return $array;
	}

	private function getNtp(): bool {
		$command = $this->commandManager->run('timedatectl | grep "NTP service"');
		$ntpSync = explode(': ', ltrim($command->getStdout()))[1];
		return $ntpSync === 'active';
	}

	/**
	 * Sets new gateway time
	 * @param bool $sync Sync time with NTP or set own time
	 * @param int|null $timestamp Unix timestamp
	 */
	public function setTime(bool $sync, ?int $timestamp = null): void {
		$this->commandManager->run('timedatectl set-ntp ' . ($sync ? 'true' : 'false'));
		if (!$sync) {
			$command = ('sudo date +%s -s @' . strval($timestamp));
			$env = ['LANG' => 'C.UTF-8'];
			$process = Process::fromShellCommandline($command, null, $env, null, null);
			$process->run();
		}
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
			array_push($array, $this->timezoneInfo($timezone));
		}
		return $array;
	}

	/**
	 * Retrieves timezone information
	 * @param string $timezone Timezone name
	 * @return array<string, string> Timezone name, abbreviation and offset
	 */
	private function timezoneInfo(string $timezone): array {
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
		$this->commandManager->run('timedatectl set-timezone ' . $timezone);
	}

}
