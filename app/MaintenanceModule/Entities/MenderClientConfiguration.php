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

namespace App\MaintenanceModule\Entities;

use InvalidArgumentException;

/**
 * Mender client configuration
 */
class MenderClientConfiguration implements IMenderConfiguration {

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
		private readonly int $version,
		private readonly array $servers,
		private readonly string $serverCertificate,
		private readonly string $tenantToken,
		private readonly int $updatePollIntervalSeconds,
		private readonly int $inventoryPollIntervalSeconds,
		private readonly int $retryPollIntervalSeconds,
	) {
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
				$config['Servers'] !== []
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
			version: $version,
			servers: $servers,
			serverCertificate: $config['ServerCertificate'] ?? '',
			tenantToken: $config['TenantToken'] ?? 'dummy',
			updatePollIntervalSeconds: $config['UpdatePollIntervalSeconds'] ?? 1800,
			inventoryPollIntervalSeconds: $config['InventoryPollIntervalSeconds'] ?? 28800,
			retryPollIntervalSeconds: $config['RetryPollIntervalSeconds'] ?? 300,
		);
	}

	/**
	 * Deserializes JSON serialized Mender client configuration
	 * @param array{config: array<string, mixed>, version: int} $json JSON serialized Mender client configuration entity
	 * @return self Mender client configuration entity
	 */
	public static function jsonDeserialize(array $json): self {
		return new self(
			version: $json['version'],
			servers: $json['config']['Servers'],
			serverCertificate: $json['config']['ServerCertificate'],
			tenantToken: $json['config']['TenantToken'],
			updatePollIntervalSeconds: $json['config']['UpdatePollIntervalSeconds'],
			inventoryPollIntervalSeconds: $json['config']['InventoryPollIntervalSeconds'],
			retryPollIntervalSeconds: $json['config']['RetryPollIntervalSeconds'],
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
