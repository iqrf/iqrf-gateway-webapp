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

use App\NetworkModule\Enums\InterfaceStates;
use App\NetworkModule\Enums\InterfaceTypes;
use JsonSerializable;

/**
 * Network interface entity
 */
final class InterfaceStatus implements JsonSerializable {

	/**
	 * @var string Network interface name
	 */
	protected $name;

	/**
	 * @var InterfaceTypes Network interface type
	 */
	protected $type;

	/**
	 * @var InterfaceStates Network interface status
	 */
	protected $state;

	/**
	 * @var string Network connection name
	 */
	protected $connectionName;

	/**
	 * Network interface entity constructor
	 * @param string $name Network interface name
	 * @param InterfaceTypes $type Network interface type
	 * @param InterfaceStates $state Network interface state
	 * @param string $connectionName Network connection name
	 */
	public function __construct(string $name, InterfaceTypes $type, InterfaceStates $state, string $connectionName) {
		$this->name = $name;
		$this->type = $type;
		$this->state = $state;
		$this->connectionName = $connectionName;
	}

	/**
	 * Creates a new network interface entity from the nmcli row
	 * @param string $string nmcli row
	 * @return InterfaceStatus Network interface
	 */
	public static function fromString(string $string): self {
		$array = explode(':', $string);
		$type = InterfaceTypes::fromScalar($array[1]);
		$state = InterfaceStates::fromScalar($array[2]);
		return new static($array[0], $type, $state, $array[3]);
	}

	/**
	 * Returns the network interface name
	 * @return string Network interface name
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Returns the network interface type
	 * @return InterfaceTypes Network interface type
	 */
	public function getType(): InterfaceTypes {
		return $this->type;
	}

	/**
	 * Returns the network interface state
	 * @return InterfaceStates Network interface state
	 */
	public function getState(): InterfaceStates {
		return $this->state;
	}

	/**
	 * Returns the network connection name
	 * @return string Network connection name
	 */
	public function getConnectionName(): string {
		return $this->connectionName;
	}

	/**
	 * Returns JSON serialized data
	 * @return array<string, string> JSON serialized data
	 */
	public function jsonSerialize(): array {
		return [
			'name' => $this->name,
			'type' => $this->type->toScalar(),
			'state' => $this->state->toScalar(),
			'connectionName' => $this->connectionName,
		];
	}

}
