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
use Nette\Utils\ArrayHash;
use Nette\Utils\Strings;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use stdClass;

/**
 * Detailed network connection entity
 */
class ConnectionDetail implements JsonSerializable {

	/**
	 * @var string Network connection ID
	 */
	private $id;

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
	 * @var IPv4Connection IPv4 network connection entity
	 */
	private $ipv4;

	/**
	 * @var IPv6Connection IPv6 network connection entity
	 */
	private $ipv6;

	/**
	 * Network connection entity constructor
	 * @param string $id Network connection ID
	 * @param UuidInterface $uuid Network connection UUID
	 * @param ConnectionTypes $type Network connection type
	 * @param string $name Network connection name
	 * @param IPv4Connection $ipv4 IPv4 network connection entity
	 * @param IPv6Connection $ipv6 IPv6 network connection entity
	 */
	public function __construct(string $id, UuidInterface $uuid, ConnectionTypes $type, string $name, IPv4Connection $ipv4, IPv6Connection $ipv6) {
		$this->id = $id;
		$this->uuid = $uuid;
		$this->type = $type;
		$this->interfaceName = $name;
		$this->ipv4 = $ipv4;
		$this->ipv6 = $ipv6;
	}

	/**
	 * Sets the values from the network connection configuration form
	 * @param stdClass|ArrayHash $form Network connection configuration form values
	 */
	public function fromForm(stdClass $form): void {
		$this->ipv4->fromForm($form->ipv4);
		$this->ipv6->fromForm($form->ipv6);
	}

	/**
	 * Creates a new detailed network connection entity from nmcli connection configuration
	 * @param string $nmCli nmcli connection configuration
	 * @return ConnectionDetail Detailed network connection entity
	 */
	public static function fromNmCli(string $nmCli): self {
		$array = explode(PHP_EOL, Strings::trim($nmCli));
		foreach ($array as $i => $row) {
			$temp = explode(':', $row, 2);
			if (Strings::startsWith($temp[0], 'connection.')) {
				$key = Strings::replace($temp[0], '~connection\.~', '');
				$array[$key] = $temp[1];
			}
			unset($array[$i]);
		}
		$uuid = Uuid::fromString($array['uuid']);
		$type = ConnectionTypes::fromScalar($array['type']);
		$ipv4 = IPv4Connection::fromNmCli($nmCli);
		$ipv6 = IPv6Connection::fromNmCli($nmCli);
		return new self($array['id'], $uuid, $type, $array['interface-name'], $ipv4, $ipv6);
	}

	/**
	 * Returns the network connection ID
	 * @return string Network connection ID
	 */
	public function getId(): string {
		return $this->id;
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
	 * Returns the network connection name
	 * @return string Network connection name
	 */
	public function getInterfaceName(): string {
		return $this->interfaceName;
	}

	/**
	 * Returns the IPv4 network connection entity
	 * @return IPv4Connection IPv4 network connection entity
	 */
	public function getIpv4(): IPv4Connection {
		return $this->ipv4;
	}

	/**
	 * Returns the IPv6 network connection entity
	 * @return IPv6Connection IPv4 network connection entity
	 */
	public function getIpv6(): IPv6Connection {
		return $this->ipv6;
	}

	/**
	 * Converts network connection entity to an array for the form
	 * @return array<string,mixed> Array for the form
	 */
	public function toForm(): array {
		return $this->jsonSerialize();
	}

	/**
	 * Converts the network connection entity to nmcli configuration string
	 * @return string nmcli configuration
	 */
	public function toNmCli(): string {
		return $this->ipv4->toNmCli() . $this->ipv6->toNmCli();
	}

	/**
	 * Returns JSON serialized data
	 * @return array<string,mixed> JSON serialized data
	 */
	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'uuid' => $this->uuid->toString(),
			'type' => $this->type->toScalar(),
			'interface-name' => $this->interfaceName,
			'ipv4' => $this->ipv4->toForm(),
			'ipv6' => $this->ipv6->toForm(),
		];
	}

}
