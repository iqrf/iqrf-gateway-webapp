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

/**
 * Mender connect configuration
 */
class MenderConnectConfiguration implements IMenderConfiguration {

	/**
	 * Constructor
	 * @param int $version Mender connect major version
	 * @param bool $fileTransfer File transfer enablement
	 * @param bool $portForward Port forwarding enablement
	 */
	public function __construct(
		private readonly int $version,
		private readonly bool $fileTransfer,
		private readonly bool $portForward,
	) {
	}

	/**
	 * Deserializes Mender connect configuration
	 * @param int $version Mender connect major version
	 * @param array<string, mixed> $config Mender connect configuration
	 * @return self Mender connect configuration entity
	 */
	public static function configDeserialize(int $version, array $config): self {
		return new self(
			version: $version,
			fileTransfer: ($config['FileTransfer']['Disable'] ?? false) !== true,
			portForward: ($config['PortForward']['Disable'] ?? false) !== true,
		);
	}

	/**
	 * Deserializes JSON serialized Mender connect configuration
	 * @param array{config: array<string, mixed>, version: int} $json JSON serialized Mender connect configuration entity
	 * @return self Mender connect configuration entity
	 */
	public static function jsonDeserialize(array $json): self {
		return new self(
			version: $json['version'],
			fileTransfer: $json['config']['FileTransfer'],
			portForward: $json['config']['PortForward'],
		);
	}

	/**
	 * Serializes Mender connect configuration
	 * @return array<string, mixed> Mender connect configuration
	 */
	public function configSerialize(): array {
		return [
			'FileTransfer' => [
				'Disable' => !$this->fileTransfer,
			],
			'PortForward' => [
				'Disable' => !$this->portForward,
			],
		];
	}

	/**
	 * Serializes JSON serialized Mender connect configuration
	 * @return array{config: array<string, mixed>, version: int} JSON serialized Mender connect configuration entity
	 */
	public function jsonSerialize(): array {
		return [
			'config' => [
				'FileTransfer' => $this->fileTransfer,
				'PortForward' => $this->portForward,
			],
			'version' => $this->version,
		];
	}

}
