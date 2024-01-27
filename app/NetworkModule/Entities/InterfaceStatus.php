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

namespace App\NetworkModule\Entities;

use App\NetworkModule\Enums\ConnectivityState;
use App\NetworkModule\Enums\InterfaceStates;
use App\NetworkModule\Enums\InterfaceTypes;
use App\NetworkModule\Utils\NmCliConnection;
use JsonSerializable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Network interface entity
 */
final class InterfaceStatus implements JsonSerializable {

	/**
	 * @var string|null MAC address
	 */
	private readonly ?string $macAddress;

	/**
	 * @var string|null Manufacturer
	 */
	private readonly ?string $manufacturer;

	/**
	 * @var string|null Model
	 */
	private readonly ?string $model;

	/**
	 * Network interface entity constructor
	 * @param string $name Network interface name
	 * @param string|null $macAddress MAC address
	 * @param string|null $manufacturer Manufacturer
	 * @param string|null $model Model
	 * @param InterfaceTypes $type Network interface type
	 * @param InterfaceStates $state Network interface state
	 * @param UuidInterface|null $connection Network connection UUID
	 * @param ConnectivityState|null $ipv4Connectivity IPv4 connectivity state
	 * @param ConnectivityState|null $ipv6Connectivity IPv6 connectivity state
	 * @param array<AvailableConnection> $availableConnections Available network connections
	 */
	public function __construct(
		private readonly string $name,
		?string $macAddress,
		?string $manufacturer,
		?string $model,
		private readonly InterfaceTypes $type,
		private readonly InterfaceStates $state,
		private readonly ?UuidInterface $connection,
		private readonly ?ConnectivityState $ipv4Connectivity,
		private readonly ?ConnectivityState $ipv6Connectivity,
		private readonly array $availableConnections = [],
	) {
		$this->macAddress = $macAddress === '' ? null : $macAddress;
		$this->manufacturer = $manufacturer === '' ? null : $manufacturer;
		$this->model = $model === '' ? null : $model;
	}

	/**
	 * Deserializes network interface entity from the nmcli row
	 * @param string $string nmcli row
	 * @return InterfaceStatus Network interface
	 */
	public static function nmCliDeserialize(string $string): self {
		$array = NmCliConnection::decode($string);
		$connection = $array['GENERAL']['CON-UUID'];
		return new self(
			name: $array['GENERAL']['DEVICE'],
			macAddress: $array['GENERAL']['HWADDR'],
			manufacturer: $array['GENERAL']['VENDOR'],
			model: $array['GENERAL']['PRODUCT'],
			type: InterfaceTypes::from($array['GENERAL']['TYPE']),
			state: InterfaceStates::fromNmCli($array['GENERAL']['STATE']),
			connection: $connection === '' ? null : Uuid::fromString($connection),
			ipv4Connectivity: array_key_exists('IP4-CONNECTIVITY', $array['GENERAL']) ? ConnectivityState::fromNmCli($array['GENERAL']['IP4-CONNECTIVITY']) : null,
			ipv6Connectivity: array_key_exists('IP6-CONNECTIVITY', $array['GENERAL']) ? ConnectivityState::fromNmCli($array['GENERAL']['IP6-CONNECTIVITY']) : null,
			availableConnections: array_map(
				static fn (string $value): AvailableConnection => AvailableConnection::nmCliDeserialize($value),
				$array['CONNECTIONS']['AVAILABLE-CONNECTIONS'] ?? [],
			),
		);
	}

	/**
	 * Returns the network interface type
	 * @return InterfaceTypes Network interface type
	 */
	public function getType(): InterfaceTypes {
		return $this->type;
	}

	/**
	 * Serializes network interface status entity into JSON
	 * @return array{name: string, type: string, state: string, connection: string|null, availableConnections: array<array{name: string, uuid: string}>, connectivity?: array{ipv4: string, ipv6: string}} JSON serialized data
	 */
	public function jsonSerialize(): array {
		$array = [
			'name' => $this->name,
			'macAddress' => $this->macAddress,
			'manufacturer' => $this->manufacturer,
			'model' => $this->model,
			'type' => $this->type->value,
			'state' => $this->state->value,
			'connection' => $this->connection?->toString(),
			'availableConnections' => array_map(
				static fn (AvailableConnection $connection): array => $connection->jsonSerialize(),
				$this->availableConnections,
			),
		];
		if ($this->ipv4Connectivity !== null && $this->ipv6Connectivity !== null) {
			$array['connectivity'] = [
				'ipv4' => $this->ipv4Connectivity->value,
				'ipv6' => $this->ipv6Connectivity->value,
			];
		}
		return $array;
	}

}
