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
	protected readonly ?string $macAddress;

	/**
	 * @var string|null Manufacturer
	 */
	protected readonly ?string $manufacturer;

	/**
	 * @var string|null Model
	 */
	protected readonly ?string $model;

	/**
	 * Network interface entity constructor
	 * @param string $name Network interface name
	 * @param string|null $macAddress MAC address
	 * @param string|null $manufacturer Manufacturer
	 * @param string|null $model Model
	 * @param InterfaceTypes $type Network interface type
	 * @param InterfaceStates $state Network interface state
	 * @param UuidInterface|null $connection Network connection UUID
	 */
	public function __construct(
		private readonly string $name,
		?string $macAddress,
		?string $manufacturer,
		?string $model,
		private readonly InterfaceTypes $type,
		private readonly InterfaceStates $state,
		private readonly ?UuidInterface $connection,
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
		$name = $array['GENERAL']['DEVICE'];
		$macAddress = $array['GENERAL']['HWADDR'];
		$manufacturer = $array['GENERAL']['VENDOR'];
		$model = $array['GENERAL']['PRODUCT'];
		$type = InterfaceTypes::from($array['GENERAL']['TYPE']);
		$state = InterfaceStates::fromNmCli($array['GENERAL']['STATE']);
		$connection = $array['GENERAL']['CON-UUID'];
		$connection = $connection === '' ? null : Uuid::fromString($connection);
		return new self($name, $macAddress, $manufacturer, $model, $type, $state, $connection);
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
	 * @return array{name: string, type: string, state: string, connection: string|null} JSON serialized data
	 */
	public function jsonSerialize(): array {
		return [
			'name' => $this->name,
			'macAddress' => $this->macAddress,
			'manufacturer' => $this->manufacturer,
			'model' => $this->model,
			'type' => $this->type->value,
			'state' => $this->state->value,
			'connection' => $this->connection?->toString(),
		];
	}

}
