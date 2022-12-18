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

namespace App\InstallModule\Models;

use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\FeatureManager;
use App\InstallModule\Entities\Dependency;

/**
 * Dependency manager
 */
class DependencyManager {

	/**
	 * @var CommandManager Command manager
	 */
	private CommandManager $commandManager;

	/**
	 * @var FeatureManager Feature manager
	 */
	private FeatureManager $featureManager;

	/**
	 * @var array<string> Enabled features
	 */
	private array $features = [];

	/**
	 * @var array<Dependency> Dependencies
	 */
	private array $dependencies;

	/**
	 * Constructor
	 * @param bool $sudo Is sudo required?
	 * @param CommandManager $commandManager Command manager
	 * @param FeatureManager $featureManager Feature manager
	 */
	public function __construct(bool $sudo, CommandManager $commandManager, FeatureManager $featureManager) {
		$this->commandManager = $commandManager;
		$this->featureManager = $featureManager;
		$this->dependencies = [
			new Dependency('apt', true, 'apt'),
			new Dependency('apt-config', true, 'apt'),
			new Dependency('apt-get', true, 'apt'),
			new Dependency('awk', true, 'gawk'),
			new Dependency('dmesg', true, 'util-linux'),
			new Dependency('find', true, 'findutils'),
			new Dependency('free', true, 'procps-ng'),
			new Dependency('git', false, 'git'),
			new Dependency('grep', true, 'grep'),
			new Dependency('hostname', true, 'net-tools'),
			new Dependency('hostnamectl', true, 'systemd'),
			new Dependency('chpasswd', true, 'passwd'),
			new Dependency('ip', true, 'iproute2'),
			new Dependency('iqrf-gateway-controller', true, 'iqrf-gateway-controller', 'iqrfGatewayController'),
			new Dependency('iqrf-gateway-setter', true, 'iqrf-gateway-setter', 'iqrfGatewaySetter'),
			new Dependency('iqrf-gateway-uploader', true, 'iqrf-gateway-uploader', 'iqrfGatewayUploader'),
			new Dependency('journalctl', true, 'systemd'),
			new Dependency('lsusb', false, 'usbutils'),
			new Dependency('mender', false, 'mender-client', 'mender'),
			new Dependency('mender-connect', false, 'mender-connect', 'mender'),
			new Dependency('mmcli', true, 'modemmanager', 'networkManager'),
			new Dependency('nmcli', false, 'network-manager', 'networkManager'),
			new Dependency('shutdown', true, 'systemd-sysv'),
			new Dependency('ssh', false, 'openssh-client', 'ssh'),
			new Dependency('ssh-keygen', false, 'openssh-client', 'ssh'),
			new Dependency('uptime', true, 'procps-ng'),
			new Dependency('wg', false, 'wireguard-tools', 'networkManager'),
		];
		if ($sudo) {
			$this->dependencies[] = new Dependency('sudo', true, 'sudo');
		}
	}

	/**
	 * Filters missing dependencies
	 * @param Dependency $dependency Dependency to check
	 * @return bool Dependency is missing
	 */
	public function filterMissing(Dependency $dependency): bool {
		$feature = $dependency->getFeature();
		if ($feature !== null && !in_array($feature, $this->features, true)) {
			return false;
		}
		return !$this->commandManager->commandExist($dependency->getCommand());
	}

	/**
	 * Returns missing dependencies
	 * @return array<Dependency> Missing dependencies
	 */
	public function listMissing(): array {
		$this->features = $this->featureManager->listEnabled();
		return array_values(array_filter($this->dependencies, [$this, 'filterMissing']));
	}

}
