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
use App\GatewayModule\Models\BoardManagers\DeviceTreeBoardManager;
use App\GatewayModule\Models\BoardManagers\DmiBoardManager;
use App\GatewayModule\Models\BoardManagers\IqrfBoardManager;
use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Exceptions\UserErrorException;
use App\IqrfNetModule\Models\EnumerationManager;
use Nette\SmartObject;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

/**
 * Tool for getting information about this gateway
 */
class InfoManager {

	use SmartObject;

	/**
	 * @var string[] Board managers
	 */
	private $boardManagers = [
		IqrfBoardManager::class,
		DeviceTreeBoardManager::class,
		DmiBoardManager::class,
	];

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * @var EnumerationManager IQMESH Enumeration manager
	 */
	private $enumerationManager;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 * @param EnumerationManager $enumerationManager IQMESH Enumeration manager
	 */
	public function __construct(CommandManager $commandManager, EnumerationManager $enumerationManager) {
		$this->commandManager = $commandManager;
		$this->enumerationManager = $enumerationManager;
	}

	/**
	 * Gets board's vendor, name and version
	 * @return string Board's vendor, name and version
	 */
	public function getBoard(): string {
		foreach ($this->boardManagers as $boardManager) {
			$boardName = (new $boardManager($this->commandManager))->getName();
			if (isset($boardName)) {
				return $boardName;
			}
		}
		return 'UNKNOWN';
	}

	/**
	 * Gets gateway ID
	 * @return string|null Gateway ID
	 */
	public function getId(): ?string {
		$gwJson = $this->commandManager->run('cat /etc/iqrf-gateway.json', true);
		if ($gwJson !== '') {
			try {
				$gw = Json::decode($gwJson, Json::FORCE_ARRAY);
				return $gw['gwId'];
			} catch (JsonException $e) {
				// Skip IQRF GW info file parsing
			}
		}
		return null;
	}

	/**
	 * Gets information about the Coordinator
	 * @return mixed[] Information about the Coordinator
	 * @throws DpaErrorException
	 * @throws EmptyResponseException
	 * @throws JsonException
	 * @throws UserErrorException
	 */
	public function getCoordinatorInfo(): array {
		$dpa = $this->enumerationManager->device(0);
		$this->parseRfMode($dpa);
		return $dpa;
	}

	/**
	 * Parses RF mode from information about the Coordinator
	 * @param mixed[] $dpa Information about the Coordinator
	 */
	private function parseRfMode(array &$dpa): void {
		if (!isset($dpa['response']['data']['rsp']['peripheralEnumeration'])) {
			return;
		}
		$enumeration = &$dpa['response']['data']['rsp']['peripheralEnumeration'];
		$flags = &$enumeration['flags'];
		if (!is_array($flags) || array_key_exists('rfMode', $flags)) {
			return;
		}
		if (version_compare($enumeration['dpaVer'], '4.00', '<')) {
			$array = &$flags['rfMode'];
		} else {
			$array = &$flags['networkType'];
		}
		if (array_key_exists('stdAndLpNetwork', $flags) && $flags['stdAndLpNetwork']) {
			$array = 'STD+LP';
		} elseif (array_key_exists('rfModeStd', $flags) && $flags['rfModeStd']) {
			$array = 'STD';
		} elseif (array_key_exists('rfModeLp', $flags) && $flags['rfModeLp']) {
			$array = 'LP';
		}
	}

	/**
	 * Gets disk usages
	 * @return string[][] Disk usages
	 */
	public function getDiskUsages(): array {
		$output = $this->commandManager->run('df -B1 -x tmpfs -x devtmpfs -T -P | awk \'{if (NR!=1) {$6="";print}}\'');
		$usages = [];
		foreach (explode(PHP_EOL, $output) as $disk) {
			$segments = explode(' ', $disk);
			$usages[] = [
				'fsName' => $segments[0],
				'fsType' => $segments[1],
				'size' => $this->convertSizes($segments[2]),
				'used' => $this->convertSizes($segments[3]),
				'available' => $this->convertSizes($segments[4]),
				'usage' => round($segments[3] / $segments[2] * 100, 2) . '%',
				'mount' => $segments[6],
			];
		}
		return $usages;
	}

	/**
	 * Gets a memory usage
	 * @return string[] Memory usage
	 */
	public function getMemoryUsage(): array {
		$output = $this->commandManager->run('free -bw | awk \'{{if (NR==2) print $2,$3,$4,$5,$6,$7,$8}}\'');
		$segments = explode(' ', $output);
		$usages = [
			'size' => $this->convertSizes($segments[0]),
			'used' => $this->convertSizes($segments[1]),
			'free' => $this->convertSizes($segments[2]),
			'shared' => $this->convertSizes($segments[3]),
			'buffers' => $this->convertSizes($segments[4]),
			'cache' => $this->convertSizes($segments[5]),
			'available' => $this->convertSizes($segments[6]),
			'usage' => round($segments[1] / $segments[0] * 100, 2) . '%',
		];
		return $usages;
	}

	/**
	 * Gets a swap usage
	 * @return string[]|null Swap usage
	 */
	public function getSwapUsage(): ?array {
		$output = $this->commandManager->run('free -b | awk \'{{if (NR==3) print $2,$3,$4}}\'');
		$segments = explode(' ', $output);
		if ($segments[0] === '0') {
			return null;
		}
		$usages = [
			'size' => $this->convertSizes($segments[0]),
			'used' => $this->convertSizes($segments[1]),
			'free' => $this->convertSizes($segments[2]),
			'usage' => round($segments[1] / $segments[0] * 100, 2) . '%',
		];
		return $usages;
	}

	/**
	 * Converts bytes to human readable sizes
	 * @param mixed $bytes Bytes to convert
	 * @param int $precision Conversion precision
	 * @return string Human readable size
	 */
	public function convertSizes($bytes, int $precision = 2): string {
		$bytes = round(floatval($bytes));
		$units = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB'];
		$unit = 'B';
		foreach ($units as $unit) {
			if (abs($bytes) < 1024 || $unit === end($units)) {
				break;
			}
			$bytes /= 1024;
		}
		return round($bytes, $precision) . ' ' . $unit;
	}

	/**
	 * Gets PIXLA token
	 * @return string|null PIXLA token
	 */
	public function getPixlaToken(): ?string {
		$gwmonId = $this->commandManager->run('cat /etc/gwman/customer_id', true);
		if ($gwmonId !== '') {
			return $gwmonId;
		}
		return null;
	}

}
