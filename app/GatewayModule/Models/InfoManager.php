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
use App\IqrfNetModule\Models\EnumerationManager;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

/**
 * Tool for getting information about this gateway
 */
class InfoManager {

	/**
	 * @var array<string> Board managers
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
	 * @return array<string, array<int|string, array<string, array<int, string>|string>|string>|string|null> Gateway information
	 */
	public function get(bool $verbose = false): array {
		return [
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
				return $json['gwId'] ?? null;
			} catch (JsonException $e) {
				// Skip IQRF GW info file parsing
			}
		}
		return null;
	}

	/**
	 * Gets information about the Coordinator
	 * @return array<mixed> Information about the Coordinator
	 * @throws DpaErrorException
	 * @throws EmptyResponseException
	 * @throws JsonException
	 */
	public function getCoordinatorInfo(): array {
		return $this->enumerationManager->device(0);
	}

	/**
	 * Gets disk usages
	 * @return array<array<string>> Disk usages
	 */
	public function getDiskUsages(): array {
		$command = 'df -l -B1 -x tmpfs -x devtmpfs -x squashfs -T -P | awk \'{if (NR!=1) {$6="";print}}\'';
		$output = $this->commandManager->run($command)->getStdout();
		$usages = [];
		foreach (explode(PHP_EOL, $output) as $disk) {
			$segments = explode(' ', $disk);
			$size = (float) $segments[2];
			$used = (float) $segments[3];
			$usages[] = [
				'fsName' => $segments[0],
				'fsType' => $segments[1],
				'size' => $this->convertSizes($size),
				'used' => $this->convertSizes($used),
				'available' => $this->convertSizes((float) $segments[4]),
				'usage' => round($used / $size * 100, 2) . '%',
				'mount' => $segments[6],
			];
		}
		return $usages;
	}

	/**
	 * Converts bytes to human readable sizes
	 * @param float $bytes Bytes to convert
	 * @param int $precision Conversion precision
	 * @return string Human readable size
	 */
	public function convertSizes(float $bytes, int $precision = 2): string {
		$units = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB'];
		foreach ($units as $unit) {
			if (abs($bytes) < 1024 || $unit === end($units)) {
				break;
			}
			$bytes /= 1024;
		}
		return round($bytes, $precision) . ' ' . ($unit ?? 'B');
	}

	/**
	 * Gets a memory usage
	 * @return array<string> Memory usage
	 */
	public function getMemoryUsage(): array {
		$command = 'free -bw | awk \'{{if (NR==2) print $2,$3,$4,$5,$6,$7,$8}}\'';
		$output = $this->commandManager->run($command)->getStdout();
		$segments = explode(' ', $output);
		return [
			'size' => $this->convertSizes((float) $segments[0]),
			'used' => $this->convertSizes((float) $segments[1]),
			'free' => $this->convertSizes((float) $segments[2]),
			'shared' => $this->convertSizes((float) $segments[3]),
			'buffers' => $this->convertSizes((float) $segments[4]),
			'cache' => $this->convertSizes((float) $segments[5]),
			'available' => $this->convertSizes((float) $segments[6]),
			'usage' => round($segments[1] / $segments[0] * 100, 2) . '%',
		];
	}

	/**
	 * Gets a swap usage
	 * @return array<string>|null Swap usage
	 */
	public function getSwapUsage(): ?array {
		$command = 'free -b | awk \'{{if (NR==3) print $2,$3,$4}}\'';
		$output = $this->commandManager->run($command)->getStdout();
		$segments = explode(' ', $output);
		if ($segments[0] === '0') {
			return null;
		}
		return [
			'size' => $this->convertSizes((float) $segments[0]),
			'used' => $this->convertSizes((float) $segments[1]),
			'free' => $this->convertSizes((float) $segments[2]),
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
