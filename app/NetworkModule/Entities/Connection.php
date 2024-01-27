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

use App\NetworkModule\Enums\ConnectionStates;
use App\NetworkModule\Enums\ConnectionTypes;
use JsonSerializable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Network connection entity
 */
final class Connection implements JsonSerializable {

	/**
	 * @var string|null Network interface name
	 */
	private readonly ?string $interfaceName;

	/**
	 * Network connection entity constructor
	 * @param string $name Network connection name
	 * @param UuidInterface $uuid Network connection UUID
	 * @param ConnectionTypes $type Network connection type
	 * @param string|null $interfaceName Network interface name
	 * @param bool $isActive Is the network connection active?
	 * @param ConnectionStates $state Network connection state
	 */
	public function __construct(
		private readonly string $name,
		private readonly UuidInterface $uuid,
		private readonly ConnectionTypes $type,
		?string $interfaceName,
		private readonly bool $isActive,
		private readonly ConnectionStates $state,
	) {
		$this->interfaceName = $interfaceName === '' ? null : $interfaceName;
	}

	/**
	 * Deserializes network connection entity from the nmcli row
	 * @param string $string nmcli row
	 * @return Connection Network connection entity
	 */
	public static function nmCliDeserialize(string $string): self {
		$array = explode(':', $string);
		$uuid = Uuid::fromString($array[1]);
		$type = ConnectionTypes::from($array[2]);
		$isActive = $array[4] === 'yes';
		$state = ConnectionStates::tryFrom($array[5]) ?? ConnectionStates::DEACTIVATED;
		return new self($array[0], $uuid, $type, $array[3], $isActive, $state);
	}

	/**
	 * Returns the network connection UUID
	 * @return UuidInterface Network connection UUID
	 */
	public function getUuid(): UuidInterface {
		return $this->uuid;
	}

	/**
	 * Returns the network connection type
	 * @return ConnectionTypes Network connection type
	 */
	public function getType(): ConnectionTypes {
		return $this->type;
	}

	/**
	 * Serializes network connection entity into JSON
	 * @return array{name: string, uuid: string, type: string, interfaceName: string|null, isActive: bool, state: string|null} JSON serialized data
	 */
	public function jsonSerialize(): array {
		return [
			'name' => $this->name,
			'uuid' => $this->uuid->toString(),
			'type' => $this->type->jsonSerialize(),
			'interfaceName' => $this->interfaceName,
			'isActive' => $this->isActive,
			'state' => $this->state->value,
		];
	}

}
