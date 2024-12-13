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

use JsonSerializable;
use Nette\Utils\Strings;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Available network connection entity
 */
final readonly class AvailableConnection implements JsonSerializable {

	/**
	 * Available network connection constructor
	 * @param string $name Network connection name
	 * @param UuidInterface $uuid Network connection UUID
	 */
	public function __construct(
		private string $name,
		private UuidInterface $uuid,
	) {
	}

	/**
	 * Deserializes available network connection from the nmcli row
	 * @param string $string nmcli row
	 * @return self Available network connection for network interface
	 */
	public static function nmCliDeserialize(string $string): self {
		$array = Strings::match($string, '#^(?<uuid>[[:xdigit:]]{8}(?:-[[:xdigit:]]{4}){3}-[[:xdigit:]]{12}) \| (?<name>.*)$#');
		return new self(
			name: $array['name'],
			uuid: Uuid::fromString($array['uuid']),
		);
	}

	/**
	 * Serializes available network connection entity into JSON
	 * @return array{name: string, uuid: string} JSON serialized data
	 */
	public function jsonSerialize(): array {
		return [
			'uuid' => $this->uuid->jsonSerialize(),
			'name' => $this->name,
		];
	}

}
