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

namespace App\CoreModule\Models;

use App\CoreModule\Exceptions\FeatureNotFoundException;
use Nette\IOException;
use Nette\Neon\Exception as NeonException;
use Nette\Neon\Neon;
use Nette\Utils\FileSystem;

/**
 * Optional feature manager
 */
class FeatureManager {

	/**
	 * @var string Path to the configuration file
	 */
	private $path;

	/**
	 * Default configuration
	 */
	private const DEFAULTS = [
		'docs' => [
			'enabled' => true,
			'url' => 'https://docs.iqrf.org/iqrf-gateway/',
		],
		'gatewayPass' => [
			'enabled' => false,
		],
		'grafana' => [
			'enabled' => false,
			'url' => '/grafana/',
		],
		'iqrfGatewayController' => [
			'enabled' => false,
		],
		'iqrfGatewayTranslator' => [
			'enabled' => false,
		],
		'mender' => [
			'enabled' => false,
		],
		'networkManager' => [
			'enabled' => false,
		],
		'nodeRed' => [
			'enabled' => false,
			'url' => '/node-red/',
		],
		'pixla' => [
			'enabled' => false,
		],
		'ssh' => [
			'enabled' => false,
		],
		'supervisord' => [
			'enabled' => false,
			'url' => 'supervisord/',
		],
		'systemdJournal' => [
			'enabled' => false,
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
	public function __construct(string $path) {
		$this->path = $path;
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
			return array_merge(self::DEFAULTS, $configuration);
		} catch (IOException | NeonException $e) {
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
		return $configuration[$name];
	}

	/**
	 * Writes the features configuration
	 * @param array<string, array<string, bool|int|string>> $features Feature configuration to write
	 * @throws IOException
	 */
	protected function write(array $features): void {
		$content = Neon::encode($features, Neon::BLOCK);
		FileSystem::write($this->path, $content);
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
	 * Checks if the feature has URL
	 * @param string $name Feature name
	 * @return bool Has the feature URL?
	 */
	public function hasUrl(string $name): bool {
		$feature = $this->get($name);
		return isset($feature['url']);
	}

	/**
	 * Lists enabled features
	 * @return array<string> Enabled features
	 */
	public function listEnabled(): array {
		$features = $this->read();
		$enabled = [];
		foreach ($features as $feature => $configuration) {
			if (!$configuration['enabled'] ?? true) {
				continue;
			}
			$enabled[] = $feature;
		}
		return $enabled;
	}

	/**
	 * Lists URL of enabled features
	 * @return array<string, string> Enabled features with URL
	 */
	public function listUrl(): array {
		$features = $this->read();
		$urls = [];
		foreach ($features as $feature => $configuration) {
			if ((!$configuration['enabled'] ?? true) || !isset($configuration['url'])) {
				continue;
			}
			$urls[$feature] = $configuration['url'];
		}
		return $urls;
	}

	/**
	 * Sets features enablement
	 * @param array<string> $names Feature names
	 * @param bool $enabled Are features enabled?
	 * @throws IOException
	 */
	public function setEnabled(array $names, bool $enabled = true): void {
		$config = $this->read();
		foreach ($names as $name) {
			if (!array_key_exists($name, $config)) {
				throw new FeatureNotFoundException($name);
			}
			$config[$name]['enabled'] = $enabled;
		}
		$this->write($config);
	}

}
