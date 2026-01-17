<?php

/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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

namespace App\CoreModule\Models;

use App\CoreModule\Exceptions\FeatureNotFoundException;
use App\GatewayModule\Models\Utils\GatewayInfoUtil;
use Nette\IOException;
use Nette\Neon\Exception as NeonException;
use Nette\Neon\Neon;
use Nette\Utils\FileSystem;
use Nette\Utils\Strings;

/**
 * Optional feature manager
 */
class FeatureManager {

	/**
	 * Default configuration
	 */
	private const DEFAULTS = [
		'apcupsd' => [
			'enabled' => false,
		],
		'docs' => [
			'enabled' => true,
			'url' => 'https://docs.iqrf.org/iqrf-gateway/',
		],
		'gatewayPass' => [
			'enabled' => false,
			'user' => 'root',
		],
		'grafana' => [
			'enabled' => false,
			'url' => '/grafana/',
		],
		'iqaros' => [
			'enabled' => false,
		],
		'iqrfCloudProvisioning' => [
			'enabled' => false,
		],
		'iqrfGatewayController' => [
			'enabled' => false,
		],
		'iqrfGatewayInfluxdbBridge' => [
			'enabled' => false,
		],
		'iqrfGatewayTranslator' => [
			'enabled' => false,
		],
		'iqrfRepository' => [
			'enabled' => false,
		],
		'journal' => [
			'enabled' => false,
			'path' => '/etc/systemd/journald.conf',
		],
		'mender' => [
			'enabled' => false,
		],
		'monit' => [
			'enabled' => false,
		],
		'networkManager' => [
			'enabled' => false,
		],
		'nodeRed' => [
			'enabled' => false,
			'url' => '/node-red/',
		],
		'remount' => [
			'enabled' => false,
		],
		'ssh' => [
			'enabled' => false,
		],
		'supervisord' => [
			'enabled' => false,
			'url' => 'supervisord/',
		],
		'trUpload' => [
			'enabled' => false,
		],
		'updater' => [
			'enabled' => false,
		],
		'unattendedUpgrades' => [
			'enabled' => false,
		],
		'versionChecker' => [
			'enabled' => false,
		],
	];

	/**
	 * Constructor
	 * @param string $path Path to the configuration file
	 */
	public function __construct(
		private readonly string $path,
		private readonly GatewayInfoUtil $gatewayInfo,
	) {
	}

	/**
	 * Checks if feature exists
	 * @param string $name Feature name
	 * @return bool True if feature exists, false otherwise
	 */
	public function existsDefault(string $name): bool {
		return array_key_exists($name, self::DEFAULTS);
	}

	/**
	 * Reads features configuration
	 * @return array<string, array<string, bool|int|string>> Features configuration
	 */
	public function read(): array {
		try {
			$content = FileSystem::read($this->path);
			$configuration = Neon::decode($content) ?? [];
			$docsUrl = ['docs' => ['url' => $this->getDocumentationUrl()]];
			return array_replace_recursive(self::DEFAULTS, $docsUrl, $configuration);
		} catch (IOException | NeonException) {
			return self::DEFAULTS;
		}
	}

	/**
	 * Edits feature configuration
	 * @param string $name Feature name
	 * @param array<string, bool|int|string> $config Feature configuration
	 * @throws FeatureNotFoundException
	 * @throws IOException
	 */
	public function edit(string $name, array $config): void {
		$configuration = $this->read();
		if (!array_key_exists($name, $configuration)) {
			throw new FeatureNotFoundException();
		}
		$configuration[$name] = $config;
		$this->write($configuration);
	}

	/**
	 * Returns feature configuration
	 * @param string $name Feature name
	 * @return array<string, bool|int|string> Feature configuration
	 * @throws FeatureNotFoundException
	 */
	public function get(string $name): array {
		$configuration = $this->read();
		if (!array_key_exists($name, $configuration)) {
			throw new FeatureNotFoundException();
		}
		$feature = $configuration[$name];
		if ($name === 'docs') {
			$feature['url'] = $this->getDocumentationUrl();
		}
		return $feature;
	}

	/**
	 * Checks if the feature is enabled
	 * @param string $name Feature name
	 * @return bool Is the feature enabled?
	 * @throws FeatureNotFoundException
	 */
	public function isEnabled(string $name): bool {
		$feature = $this->get($name);
		return $feature['enabled'];
	}

	/**
	 * Lists enabled features
	 * @return array<string> Enabled features
	 */
	public function listEnabled(): array {
		return array_keys(array_filter($this->read(), static fn (array $configuration): bool => ($configuration['enabled'] ?? false)));
	}

	/**
	 * Sets features enablement
	 * @param array<string> $names Feature names
	 * @param bool $enabled Are features enabled?
	 * @throws FeatureNotFoundException
	 * @throws IOException
	 */
	public function setEnabled(array $names, bool $enabled): void {
		$config = $this->read();
		foreach ($names as $name) {
			if (!array_key_exists($name, $config)) {
				throw new FeatureNotFoundException($name);
			}
			$config[$name]['enabled'] = $enabled;
		}
		$this->write($config);
	}

	/**
	 * Writes the features configuration
	 * @param array<string, array<string, bool|int|string>> $features Feature configuration to write
	 * @throws IOException
	 */
	protected function write(array $features): void {
		$content = Neon::encode($features, blockMode: true);
		FileSystem::write($this->path, $content);
	}

	/**
	 * Gets documentation URL based on the gateway image and product
	 * @return string Documentation URL based on the gateway image and product
	 */
	private function getDocumentationUrl(): string {
		$imageRegex = '/^(?P<product>industrial|iqaros|iqube|litework)-(?P<variant>armbian|yocto)-(?P<version>v\d+\.\d+\.\d+(-(alpha|beta|rc)\d*)?)$/';
		$imageMatch = Strings::match($this->gatewayInfo->getImage(), $imageRegex);

		if ($imageMatch['product'] === 'iqaros') {
			return 'https://iqaros.eu/installation2.html';
		}

		return match ($this->gatewayInfo->getProduct()) {
			'IQD-GW-01', 'IQD-GW-02' => 'https://docs.iqrf.org/iqube/',
			'IQD-GW04' => 'https://docs.iqrf.org/industrial/',
			default => 'https://docs.iqrf.org/iqrf-gateway/',
		};
	}

}
