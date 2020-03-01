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

namespace App\NetworkModule\Entities;

use App\NetworkModule\Enums\ConnectionTypes;
use JsonSerializable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Network connection entity
 */
final class Connection implements JsonSerializable {

	/**
	 * @var string Network connection name
	 */
	private $name;

	/**
	 * @var UuidInterface Network connection UUID
	 */
	private $uuid;

	/**
	 * @var ConnectionTypes Network connection type
	 */
	private $type;

	/**
	 * @var string Network interface name
	 */
	private $interfaceName;

	/**
	 * Network connection entity constructor
	 * @param string $name Network connection name
	 * @param UuidInterface $uuid Network connection UUID
	 * @param ConnectionTypes $type Network connection type
	 * @param string $interfaceName Network interface name
	 */
	public function __construct(string $name, UuidInterface $uuid, ConnectionTypes $type, string $interfaceName) {
		$this->name = $name;
		$this->uuid = $uuid;
		$this->type = $type;
		$this->interfaceName = $interfaceName;
	}

	/**
	 * Creates a new network connection entity from the nmcli row
	 * @param string $string nmcli row
	 * @return Connection Network connection entity
	 */
	public static function fromString(string $string): self {
		$array = explode(':', $string);
		$uuid = Uuid::fromString($array[1]);
		$type = ConnectionTypes::fromScalar($array[2]);
		return new static($array[0], $uuid, $type, $array[3]);
	}

	/**
	 * Returns the network connection name
	 * @return string Network connection name
	 */
	public function getName(): string {
		return $this->name;
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
	 * Returns the network interface name
	 * @return string Network interface name
	 */
	public function getInterfaceName(): string {
		return $this->interfaceName;
	}

	/**
	 * Returns JSON serialized data
	 * @return array<string,mixed> JSON serialized data
	 */
	public function jsonSerialize(): array {
		return [
			'name' => $this->name,
			'uuid' => $this->uuid,
			'type' => $this->type->toScalar(),
		];
	}

}
