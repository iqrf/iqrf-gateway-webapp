<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2018 IQRF Tech s.r.o.
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
use App\CoreModule\Models\VersionManager;
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
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * @var EnumerationManager IQMESH Enumeration manager
	 */
	private $enumerationManager;

	/**
	 * @var VersionManager Version manager
	 */
	private $versionManager;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 * @param EnumerationManager $enumerationManager IQMESH Enumeration manager
	 * @param VersionManager $versionManager Version manager
	 */
	public function __construct(CommandManager $commandManager, EnumerationManager $enumerationManager, VersionManager $versionManager) {
		$this->commandManager = $commandManager;
		$this->versionManager = $versionManager;
		$this->enumerationManager = $enumerationManager;
	}

	/**
	 * Get board's vendor, name and version
	 * @return string Board's vendor, name and version
	 */
	public function getBoard(): string {
		$gwJson = $this->commandManager->send('cat /etc/iqrf-gateway.json', true);
		if ($gwJson !== '') {
			try {
				$gw = Json::decode($gwJson, Json::FORCE_ARRAY);
				return $gw['gwManufacturer'] . ' ' . $gw['gwProduct'];
			} catch (JsonException $e) {
				// Skip IQRF GW info file parsing
			}
		}
		$deviceTree = $this->commandManager->send('cat /proc/device-tree/model', true);
		if ($deviceTree !== '') {
			return $deviceTree;
		}
		$dmiBoardVendor = $this->commandManager->send('cat /sys/class/dmi/id/board_vendor', true);
		$dmiBoardName = $this->commandManager->send('cat /sys/class/dmi/id/board_name', true);
		$dmiBoardVersion = $this->commandManager->send('cat /sys/class/dmi/id/board_version', true);
		if ($dmiBoardName !== '' && $dmiBoardVendor !== '' && $dmiBoardVersion !== '') {
			return $dmiBoardVendor . ' ' . $dmiBoardName . ' (' . $dmiBoardVersion . ')';
		}
		return 'UNKNOWN';
	}

	/**
	 * Get gateway ID
	 * @return string|null Gateway ID
	 */
	public function getId(): ?string {
		$gwJson = $this->commandManager->send('cat /etc/iqrf-gateway.json', true);
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
	 * Get IPv4 and IPv6 addresses of the gateway
	 * @return string[][] IPv4 and IPv6 addresses
	 */
	public function getIpAddresses(): array {
		$addresses = [];
		$lsInterfaces = $this->commandManager->send('ls /sys/class/net | awk \'{ print $0 }\'', true);
		$interfaces = explode(PHP_EOL, $lsInterfaces);
		foreach ($interfaces as $interface) {
			if ($interface === 'lo') {
				continue;
			}
			$cmd = 'ip a s ' . $interface . ' | grep inet | grep global | grep -v temporary | awk \'{print $2}\'';
			$output = $this->commandManager->send($cmd, true);
			if ($output !== '') {
				$addresses[$interface] = explode(PHP_EOL, $output);
			}
		}
		return $addresses;
	}

	/**
	 * Get MAC addresses of the gateway
	 * @return string[] MAC addresses array
	 */
	public function getMacAddresses(): array {
		$addresses = [];
		$lsInterfaces = $this->commandManager->send('ls /sys/class/net | awk \'{ print $0 }\'', true);
		$interfaces = explode(PHP_EOL, $lsInterfaces);
		foreach ($interfaces as $interface) {
			if ($interface === 'lo') {
				continue;
			}
			$cmd = 'cat /sys/class/net/' . $interface . '/address';
			$addresses[$interface] = $this->commandManager->send($cmd, true);
		}
		return $addresses;
	}

	/**
	 * Get version of the daemon
	 * @return string IQRF Daemon version
	 */
	public function getDaemonVersion(): string {
		$cmd = 'iqrfgd2 version';
		$daemonExistence = $this->commandManager->commandExist('iqrfgd2');
		if (!$daemonExistence) {
			return 'none';
		}
		$result = $this->commandManager->send($cmd);
		if ($result !== '') {
			return $result;
		}
		return 'unknown';
	}

	/**
	 * Get hostname of the gateway
	 * @return string Hostname
	 */
	public function getHostname(): string {
		$cmd = 'hostname -f';
		return $this->commandManager->send($cmd);
	}

	/**
	 * Get information about the Coordinator
	 * @return mixed[] Information about the Coordinator
	 * @throws DpaErrorException
	 * @throws EmptyResponseException
	 * @throws JsonException
	 * @throws UserErrorException
	 */
	public function getCoordinatorInfo(): array {
		return $this->enumerationManager->device(0);
	}

	/**
	 * Get current version of this wab application
	 * @return string Version of this web application
	 * @throws JsonException
	 */
	public function getWebAppVersion(): string {
		return $this->versionManager->getInstalledWebapp();
	}

	/**
	 * Get disk usages
	 * @return string[][] Disk usages
	 */
	public function getDiskUsages(): array {
		$output = $this->commandManager->send('df -B1 -x tmpfs -x devtmpfs -T -P | awk \'{if (NR!=1) {$6="";print}}\'');
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
	 * Get memory usage
	 * @return string[] Memory usage
	 */
	public function getMemoryUsage(): array {
		$output = $this->commandManager->send('free -bw | awk \'{{if (NR==2) print $2,$3,$4,$5,$6,$7,$8}}\'');
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
	 * Get swap usage
	 * @return string[]|null Swap usage
	 */
	public function getSwapUsage(): ?array {
		$output = $this->commandManager->send('free -b | awk \'{{if (NR==3) print $2,$3,$4}}\'');
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

}
