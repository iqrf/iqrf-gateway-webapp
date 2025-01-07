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

namespace App\NetworkModule\Entities;

use App\NetworkModule\Utils\NmCliConnection;
use stdClass;

/**
 * VLAN configuration
 */
class VlanConfiguration implements INetworkManagerEntity {

	/**
	 * nmcli configuration prefix
	 */
	private const NMCLI_PREFIX = 'vlan';

	/**
	 * @param string $parentInterface Parent Ethernet interface
	 * @param int $id VLAN ID
	 * @param VlanFlags $flags VLAN flags
	 */
	public function __construct(
		private readonly string $parentInterface,
		private readonly int $id,
		private readonly VlanFlags $flags,
	) {
	}

	/**
	 * Deserialize VLAN configuration from JSON
	 * @param stdClass $json JSON serialized VLAN configuration
	 * @return self VLAN configuration entity
	 */
	public static function jsonDeserialize(stdClass $json): self {
		return new self(
			$json->parentInterface,
			$json->id,
			VlanFlags::jsonDeserialize($json->flags),
		);
	}

	/**
	 * Deserializes VLAN configuration from nmcli configuration
	 * @param array<string, array<string, array<string>|string>> $nmCli nmcli VLAN configuration
	 * @return self VLAN configuration entity
	 */
	public static function nmCliDeserialize(array $nmCli): self {
		$array = $nmCli[self::NMCLI_PREFIX];
		return new self(
			$array['parent'],
			(int) $array['id'],
			VlanFlags::nmCliDeserialize((int) $array['flags']),
		);
	}

	/**
	 * Serializes VLAN configuration into nmcli configuration
	 * @return string VLAN configuration serialized into nmcli configuration
	 */
	public function nmCliSerialize(): string {
		$array = [
			'parent' => $this->parentInterface,
			'id' => $this->id,
			'flags' => $this->flags->nmCliSerialize(),
		];
		return NmCliConnection::encode($array, self::NMCLI_PREFIX);
	}

	/**
	 * Serializes VLAN configuration entity into JSON
	 * @return array{parentInterface: string, id: int, flags: array{reorderHeaders: bool, gvrp: bool, looseBinding: bool, mvrp: bool}} JSON serialized VLAN configuration
	 */
	public function jsonSerialize(): array {
		return [
			'parentInterface' => $this->parentInterface,
			'id' => $this->id,
			'flags' => $this->flags->jsonSerialize(),
		];
	}

}
