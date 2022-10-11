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

use App\CoreModule\Models\CommandManager;
use App\GatewayModule\Models\BoardManagers\DeviceTreeBoardManager;
use App\GatewayModule\Models\BoardManagers\DmiBoardManager;
use App\GatewayModule\Models\BoardManagers\IqrfBoardManager;
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
	 * @param NetworkManager $networkManager Network manager
	 * @param VersionManager $versionManager Version manager
	 */
	public function __construct(CommandManager $commandManager, NetworkManager $networkManager, VersionManager $versionManager) {
		$this->commandManager = $commandManager;
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
			'gwImage' => $this->getImage(),
			'versions' => [
				'controller' => $this->versionManager->getController(),
				'daemon' => $this->versionManager->getDaemon($verbose),
				'setter' => $this->versionManager->getSetter(),
				'uploader' => $this->versionManager->getUploader(),
				'webapp' => $this->versionManager->getWebapp($verbose),
			],
			'hostname' => $this->networkManager->getHostname(),
			'interfaces' => $this->networkManager->getInterfaces(),
			'diskUsages' => $this->getDiskUsages(),
			'memoryUsage' => $this->getMemoryUsage(),
			'swapUsage' => $this->getSwapUsage(),
			'uptime' => $this->getUptime(),
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
	 * Reads IQRF Gateway file and returns it's contents
	 * @return array<string, string>
	 */
	public function readGatewayFile(): ?array {
		$command = 'cat /etc/iqrf-gateway.json';
		$output = $this->commandManager->run($command, true)->getStdout();
		if ($output !== '') {
			try {
				return Json::decode($output, Json::FORCE_ARRAY);
			} catch (JsonException $e) {
				// Skip IQRF GW info file parsing
			}
		}
		return null;
	}

	/**
	 * Gets IQRF Gateway ID
	 * @return string|null IQRF Gateway ID
	 */
	public function getId(): ?string {
		return $this->readGatewayFile()['gwId'] ?? null;
	}

	/**
	 * Gets IQRF Gateway Image
	 */
	public function getImage(): ?string {
		return $this->readGatewayFile()['gwImage'] ?? null;
	}

	/**
	 * Gets gateway uptime
	 * @return string Gateway uptime
	 */
	public function getUptime(): string {
		return $this->commandManager->run('uptime -p')->getStdout();
	}

	/**
	 * Gets disk usages
	 * @return array<array<string>> Disk usages
	 */
	public function getDiskUsages(): array {
		$command = 'df -l -B1 -x tmpfs -x devtmpfs -x overlay -x squashfs -T -P | awk \'{if (NR!=1) {$6="";print}}\'';
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
		$size = (float) $segments[0];
		$used = (float) $segments[1];
		return [
			'size' => $this->convertSizes($size),
			'used' => $this->convertSizes($used),
			'free' => $this->convertSizes((float) $segments[2]),
			'shared' => $this->convertSizes((float) $segments[3]),
			'buffers' => $this->convertSizes((float) $segments[4]),
			'cache' => $this->convertSizes((float) $segments[5]),
			'available' => $this->convertSizes((float) $segments[6]),
			'usage' => round($used / $size * 100, 2) . '%',
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
		$size = (float) $segments[0];
		$used = (float) $segments[1];
		return [
			'size' => $this->convertSizes($size),
			'used' => $this->convertSizes($used),
			'free' => $this->convertSizes((float) $segments[2]),
			'usage' => round($used / $size * 100, 2) . '%',
		];
	}

	/**
	 * Returns the gateway's hostname
	 * @return string Hostname
	 */
	public function getHostname(): string {
		return $this->networkManager->getHostname();
	}

}
