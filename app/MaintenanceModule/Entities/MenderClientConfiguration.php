<?php

/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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

namespace App\MaintenanceModule\Entities;

use InvalidArgumentException;

/**
 * Mender client configuration
 */
class MenderClientConfiguration implements IMenderConfiguration {

	/**
	 * @var int Mender client major version
	 */
	private int $version;

	/**
	 * @var array<string> Mender server URLs
	 */
	private array $servers;

	/**
	 * @var string Mender server certificate
	 */
	private string $serverCertificate;

	/**
	 * @var string Mender tenant token
	 */
	private string $tenantToken;

	/**
	 * @var int Mender update poll interval in seconds
	 */
	private int $updatePollIntervalSeconds;

	/**
	 * @var int Mender inventory poll interval in seconds
	 */
	private int $inventoryPollIntervalSeconds;

	/**
	 * @var int Mender retry poll interval in seconds
	 */
	private int $retryPollIntervalSeconds;

	/**
	 * Constructor
	 * @param int $version Mender client major version
	 * @param array<string> $servers Mender server URLs
	 * @param string $serverCertificate Mender server certificate
	 * @param string $tenantToken Mender tenant token
	 * @param int $updatePollIntervalSeconds Mender update poll interval in seconds
	 * @param int $inventoryPollIntervalSeconds Mender inventory poll interval in seconds
	 * @param int $retryPollIntervalSeconds Mender retry poll interval in seconds
	 */
	public function __construct(
		int $version,
		array $servers,
		string $serverCertificate,
		string $tenantToken,
		int $updatePollIntervalSeconds,
		int $inventoryPollIntervalSeconds,
		int $retryPollIntervalSeconds
	) {
		$this->version = $version;
		$this->servers = $servers;
		$this->serverCertificate = $serverCertificate;
		$this->tenantToken = $tenantToken;
		$this->updatePollIntervalSeconds = $updatePollIntervalSeconds;
		$this->inventoryPollIntervalSeconds = $inventoryPollIntervalSeconds;
		$this->retryPollIntervalSeconds = $retryPollIntervalSeconds;
	}

	/**
	 * Deserializes Mender client configuration
	 * @param int $version Mender client major version
	 * @param array<string, mixed> $config Mender client configuration
	 * @return self Mender client configuration entity
	 */
	public static function configDeserialize(int $version, array $config): self {
		if (in_array($version, [2, 3], true)) {
			if (array_key_exists('ServerURL', $config)) {
				$servers = [$config['ServerURL']];
			} elseif (
				array_key_exists('Servers', $config) &&
				is_array($config['Servers']) &&
				count($config['Servers']) !== 0
			) {
				$servers = [$config['Servers'][0]['ServerURL']];
			} else {
				$servers = ['https://hosted.mender.io'];
			}
		} elseif ($version === 4) {
			if (array_key_exists('Servers', $config)) {
				$servers = array_map(static fn (array $server): string => $server['ServerURL'], $config['Servers']);
			} else {
				$servers = [$config['ServerURL'] ?? 'https://hosted.mender.io'];
			}
		} else {
			throw new InvalidArgumentException('Unsupported Mender client configuration version.');
		}
		return new self(
			$version,
			$servers,
			$config['ServerCertificate'] ?? '',
			$config['TenantToken'] ?? 'dummy',
			$config['UpdatePollIntervalSeconds'] ?? 1800,
			$config['InventoryPollIntervalSeconds'] ?? 28800,
			$config['RetryPollIntervalSeconds'] ?? 300,
		);
	}

	/**
	 * Deserializes JSON serialized Mender client configuration
	 * @param array{config: array<string, mixed>, version: int} $json JSON serialized Mender client configuration entity
	 * @return self Mender client configuration entity
	 */
	public static function jsonDeserialize(array $json): self {
		return new self(
			$json['version'],
			$json['config']['Servers'],
			$json['config']['ServerCertificate'],
			$json['config']['TenantToken'],
			$json['config']['UpdatePollIntervalSeconds'],
			$json['config']['InventoryPollIntervalSeconds'],
			$json['config']['RetryPollIntervalSeconds'],
		);
	}

	/**
	 * Fixes up Mender client configuration
	 * @param int $version Mender client major version
	 * @param array<string, mixed> $config Mender client configuration
	 * @return array<string, mixed> Fixed up Mender client configuration
	 */
	public static function configFixUp(int $version, array $config): array {
		if (in_array($version, [2, 3], true)) {
			if (array_key_exists('Servers', $config) && !array_key_exists('ServerURL', $config)) {
				$config['ServerURL'] = $config['Servers'][0]['ServerURL'];
			}
			unset($config['Servers']);
		} elseif ($version === 4) {
			if (array_key_exists('ServerURL', $config) && !array_key_exists('Servers', $config)) {
				$config['Servers'] = [['ServerURL' => $config['ServerURL']]];
			}
			unset($config['ServerURL']);
		} else {
			throw new InvalidArgumentException('Unsupported Mender client configuration version.');
		}
		return $config;
	}

	/**
	 * Serializes Mender client configuration
	 * @return array<string, mixed> Mender client configuration
	 */
	public function configSerialize(): array {
		$config = [
			'ServerCertificate' => $this->serverCertificate,
			'TenantToken' => $this->tenantToken,
			'UpdatePollIntervalSeconds' => $this->updatePollIntervalSeconds,
			'InventoryPollIntervalSeconds' => $this->inventoryPollIntervalSeconds,
			'RetryPollIntervalSeconds' => $this->retryPollIntervalSeconds,
		];
		if ($this->version === 4) {
			$config['Servers'] = array_map(static fn (string $server): array => ['ServerURL' => $server], $this->servers);
		} else {
			$config['ServerURL'] = $this->servers[0];
		}
		return $config;
	}

	/**
	 * Serializes JSON serialized Mender client configuration
	 * @return array{config: array<string, mixed>, version: int} JSON serialized Mender client configuration entity
	 */
	public function jsonSerialize(): array {
		return [
			'version' => $this->version,
			'config' => [
				'Servers' => $this->servers,
				'ServerCertificate' => $this->serverCertificate,
				'TenantToken' => $this->tenantToken,
				'UpdatePollIntervalSeconds' => $this->updatePollIntervalSeconds,
				'InventoryPollIntervalSeconds' => $this->inventoryPollIntervalSeconds,
				'RetryPollIntervalSeconds' => $this->retryPollIntervalSeconds,
			],
		];
	}

}
