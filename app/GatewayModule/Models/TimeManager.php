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

namespace App\GatewayModule\Models;

use App\ConfigModule\Utils\ConfParser;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\PrivilegedFileManager;
use App\GatewayModule\Exceptions\ConfNotFoundException;
use App\GatewayModule\Exceptions\InvalidConfFormatException;
use App\GatewayModule\Exceptions\NonexistentTimezoneException;
use App\GatewayModule\Exceptions\TimeDateException;
use DateTime;
use DateTimeZone;
use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Throwable;

/**
 * Time manager
 */
class TimeManager {

	/**
	 * @var array<string, array<string, string|int>> Default timesyncd configuration
	 */
	private const TIMESYNCD_DEFAULT = [
		'Time' => [
			'NTP' => '',
			'FallbackNTP' => '',
			'RootDistanceMaxSec' => 5,
			'PollIntervalMinSec' => 32,
			'PollIntervalMaxSec' => 2048,
		],
	];

	/**
	 * @var string Timesyncd configuration file name
	 */
	private string $confFile;

	/**
	 * @var CommandManager Command manager
	 */
	private CommandManager $commandManager;

	/**
	 * @var PrivilegedFileManager $fileManager Privileged file manager
	 */
	private PrivilegedFileManager $fileManager;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 */
	public function __construct(CommandManager $commandManager, string $timesyndPath) {
		$this->commandManager = $commandManager;
		$this->confFile = basename($timesyndPath);
		$this->fileManager = new PrivilegedFileManager(dirname($timesyndPath), $commandManager);
	}

	/**
	 * Returns gateway date, time, timezone and NTP configuration
	 * @return array<string, int|string|bool|array<string>> Time configuration
	 */
	public function getTime(): array {
		$timezone = Strings::trim(FileSystem::read('/etc/timezone'));
		$date = new DateTime('now', new DateTimeZone($timezone));
		$status = $this->getStatus();
		$timesyncConf = $this->readTimesyncd();
		$tokens = explode(';', $date->format('e;T;O;Z;U;Y-m-d H:i:s'));
		$array = [
			'zoneName' => $tokens[0],
			'abbrevation' => $tokens[1],
			'gmtOffset' => $tokens[2],
			'gmtOffsetSec' => intval($tokens[3]),
			'formattedZone' => sprintf('%s (%s, %s)', $tokens[0], $tokens[1], $tokens[2]),
			'utcTimestamp' => intval($tokens[4]),
			'localTimestamp' => intval($tokens[4]) + intval($tokens[3]),
			'formattedTime' => $tokens[5],
		];
		$array['ntpSync'] = $status['NTP'];
		$array['ntpServers'] = strlen($timesyncConf['Time']['NTP']) === 0 ? [] : explode(' ', $timesyncConf['Time']['NTP']);
		return $array;
	}

	/**
	 * Sets gateway date, time, timezone and NTP configuration
	 * @param array<string, bool|string|array<string>> $time Time configuration
	 */
	public function setTime(array $time): void {
		if (array_key_exists('zoneName', $time)) {
			$this->setTimezone($time['zoneName']);
		}
		if ($time['ntpSync']) {
			$this->storeTimesyncd($time['ntpServers']);
			$this->setNtp(true);
		} else {
			$this->setNtp(false);
			$this->setDateTime($time['datetime']);
		}
	}

	/**
	 * Sets date and time
	 * @param string $datetime ISO8601 datetime string
	 */
	private function setDateTime(string $datetime): void {
		$command = $this->commandManager->run(sprintf('date --set="%s"', $datetime), false, 0);
		if ($command->getExitCode() !== 0) {
			throw new TimeDateException($command->getStderr());
		}
	}

	/**
	 * Returns timedatectl status
	 * @return array<string, mixed|array<string, mixed>> Timedatectl status
	 * @throws TimeDateException
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
	 * @return array{name: string, code: string, offset: string} Timezone name, abbreviation and offset
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
	 * @throws NonexistentTimezoneException
	 */
	public function setTimezone(string $timezone): void {
		$command = $this->commandManager->run('timedatectl set-timezone ' . $timezone, true);
		if ($command->getExitCode() !== 0) {
			throw new NonexistentTimezoneException($command->getStderr());
		}
	}

	/**
	 * Sets NTP synchronization
	 * @param bool $enabled NTP sync status
	 */
	public function setNtp(bool $enabled): void {
		$command = $this->commandManager->run('timedatectl set-ntp ' . ($enabled ? 'true' : 'false'), true);
		if ($command->getExitCode() !== 0) {
			throw new TimeDateException($command->getStderr());
		}
	}

	/**
	 * Parses and returns NTP configuration from timesyncd service
	 * @return array<string, array<string, mixed>> NTP configuration
	 * @throws ConfNotFoundException
	 * @throws InvalidConfFormatException
	 */
	private function readTimesyncd(): array {
		if (!$this->fileManager->exists($this->confFile)) {
			throw new ConfNotFoundException('Timesyncd configuration file not found.');
		}
		$config = ConfParser::toArray($this->fileManager->read($this->confFile));
		if ($config === null) {
			throw new InvalidConfFormatException('Invalid configuration file format.');
		}
		return array_replace_recursive(self::TIMESYNCD_DEFAULT, $config);
	}

	/**
	 * Converts and stores NTP configuration for timesyncd service
	 * @param array<string> $servers NTP servers
	 * @throws ConfNotFoundException
	 * @throws InvalidConfFormatException
	 */
	private function storeTimesyncd(array $servers): void {
		$current = $this->readTimesyncd();
		$serverList = implode(' ', $servers);
		$current['Time']['NTP'] = $serverList === '' ? null : $serverList;
		$this->fileManager->write($this->confFile, ConfParser::toConf($current));
	}

}
