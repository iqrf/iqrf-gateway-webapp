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
	 * @var NetworkManager Network manager
	 */
	private $networkManager;

	/**
	 * @var VersionManager Version manager
	 */
	private $versionManager;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 * @param EnumerationManager $enumerationManager IQMESH Enumeration manager
	 * @param NetworkManager $networkManager Network manager
	 * @param VersionManager $versionManager Version manager
	 */
	public function __construct(CommandManager $commandManager, EnumerationManager $enumerationManager, NetworkManager $networkManager, VersionManager $versionManager) {
		$this->commandManager = $commandManager;
		$this->enumerationManager = $enumerationManager;
		$this->networkManager = $networkManager;
		$this->versionManager = $versionManager;
	}

	/**
	 * Returns information about the gateway
	 * @param bool $verbose Verbose output
	 * @return array<string,mixed> Gateway information
	 */
	public function get(bool $verbose = false): array {
		$info = [
			'board' => $this->getBoard(),
			'gwId' => $this->getId(),
			'pixla' => $this->getPixlaToken(),
			'versions' => [
				'controller' => $this->versionManager->getController(),
				'daemon' => $this->versionManager->getDaemon($verbose),
				'webapp' => $this->versionManager->getWebapp($verbose),
			],
			'hostname' => $this->networkManager->getHostname(),
			'interfaces' => $this->networkManager->getInterfaces(),
			'diskUsages' => $this->getDiskUsages(),
			'memoryUsage' => $this->getMemoryUsage(),
			'swapUsage' => $this->getSwapUsage(),
		];
		try {
			$info['coordinator'] = $this->getCoordinatorInfo();
		} catch (DpaErrorException | EmptyResponseException | JsonException $e) {
			$info['coordinator'] = null;
		}
		return $info;
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
		$command = 'cat /etc/iqrf-gateway.json';
		$output = $this->commandManager->run($command, true)->getStdout();
		if ($output !== '') {
			try {
				$json = Json::decode($output, Json::FORCE_ARRAY);
				return $json['gwId'];
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
		if (!isset($dpa['response']->data->rsp->peripheralEnumeration)) {
			return;
		}
		$enumeration = &$dpa['response']->data->rsp->peripheralEnumeration;
		$flags = &$enumeration->flags;
		if (version_compare($enumeration->dpaVer, '4.00', '<')) {
			$array = &$flags->rfMode;
		} else {
			$array = &$flags->networkType;
		}
		if (isset($flags->stdAndLpNetwork) && $flags->stdAndLpNetwork) {
			$array = 'STD+LP';
		} elseif (isset($flags->rfModeStd) && $flags->rfModeStd) {
			$array = 'STD';
		} elseif (isset($flags->rfModeLp) && $flags->rfModeLp) {
			$array = 'LP';
		}
	}

	/**
	 * Gets disk usages
	 * @return string[][] Disk usages
	 */
	public function getDiskUsages(): array {
		$command = 'df -l -B1 -x tmpfs -x devtmpfs -T -P | awk \'{if (NR!=1) {$6="";print}}\'';
		$output = $this->commandManager->run($command)->getStdout();
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
	 * Gets a memory usage
	 * @return string[] Memory usage
	 */
	public function getMemoryUsage(): array {
		$command = 'free -bw | awk \'{{if (NR==2) print $2,$3,$4,$5,$6,$7,$8}}\'';
		$output = $this->commandManager->run($command)->getStdout();
		$segments = explode(' ', $output);
		return [
			'size' => $this->convertSizes($segments[0]),
			'used' => $this->convertSizes($segments[1]),
			'free' => $this->convertSizes($segments[2]),
			'shared' => $this->convertSizes($segments[3]),
			'buffers' => $this->convertSizes($segments[4]),
			'cache' => $this->convertSizes($segments[5]),
			'available' => $this->convertSizes($segments[6]),
			'usage' => round($segments[1] / $segments[0] * 100, 2) . '%',
		];
	}

	/**
	 * Gets a swap usage
	 * @return string[]|null Swap usage
	 */
	public function getSwapUsage(): ?array {
		$command = 'free -b | awk \'{{if (NR==3) print $2,$3,$4}}\'';
		$output = $this->commandManager->run($command)->getStdout();
		$segments = explode(' ', $output);
		if ($segments[0] === '0') {
			return null;
		}
		return [
			'size' => $this->convertSizes($segments[0]),
			'used' => $this->convertSizes($segments[1]),
			'free' => $this->convertSizes($segments[2]),
			'usage' => round($segments[1] / $segments[0] * 100, 2) . '%',
		];
	}

	/**
	 * Gets PIXLA token
	 * @return string|null PIXLA token
	 */
	public function getPixlaToken(): ?string {
		$command = 'cat /etc/gwman/customer_id';
		$output = $this->commandManager->run($command, true)->getStdout();
		if ($output !== '') {
			return $output;
		}
		return null;
	}

}
