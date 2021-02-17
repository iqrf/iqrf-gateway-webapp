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
use App\NetworkModule\Utils\NmCliConnection;
use Nette\Utils\ArrayHash;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use stdClass;

/**
 * Detailed network connection entity
 */
class ConnectionDetail implements INetworkManagerEntity {

	/**
	 * nmcli configuration prefix
	 */
	public const NMCLI_PREFIX = 'connection';

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
	 * @var AutoConnect Automatic connection entity
	 */
	private $autoConnect;

	/**
	 * @var IPv4Connection IPv4 network connection entity
	 */
	private $ipv4;

	/**
	 * @var IPv6Connection IPv6 network connection entity
	 */
	private $ipv6;

	/**
	 * @var WifiConnection|null WiFi network connection entity
	 */
	private $wifi;

	/**
	 * Network connection entity constructor
	 * @param string $name Network connection name
	 * @param UuidInterface $uuid Network connection UUID
	 * @param ConnectionTypes $type Network connection type
	 * @param string $interface Network interface name
	 * @param AutoConnect $autoConnect Automatic connection entity
	 * @param IPv4Connection $ipv4 IPv4 network connection entity
	 * @param IPv6Connection $ipv6 IPv6 network connection entity
	 * @param WifiConnection|null $wifi WiFi network connection entity
	 */
	public function __construct(?string $name, UuidInterface $uuid, ConnectionTypes $type, string $interface, AutoConnect $autoConnect, IPv4Connection $ipv4, IPv6Connection $ipv6, ?WifiConnection $wifi = null) {
		$this->name = $name;
		$this->uuid = $uuid;
		$this->type = $type;
		$this->interfaceName = $interface;
		$this->autoConnect = $autoConnect;
		$this->ipv4 = $ipv4;
		$this->ipv6 = $ipv6;
		$this->wifi = $wifi;
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
	 * Returns the network connection name
	 * @return string Network connection name
	 */
	public function getInterfaceName(): string {
		return $this->interfaceName;
	}

	/**
	 * Deserializes network connection entity from JSON
	 * @param stdClass|ArrayHash $json Network connection configuration form values
	 */
	public static function jsonDeserialize(stdClass $json): INetworkManagerEntity {
		$uuid = Uuid::fromString($json->uuid);
		$autoConnect = AutoConnect::jsonDeserialize($json->autoConnect);
		$ipv4 = IPv4Connection::jsonDeserialize($json->ipv4);
		$ipv6 = IPv6Connection::jsonDeserialize($json->ipv6);
		$type = ConnectionTypes::fromScalar($json->type);
		$wifi = null;
		if ($type->equals(ConnectionTypes::WIFI())) {
			$wifi = WifiConnection::jsonDeserialize($json->wifi);
		}
		return new self($json->name, $uuid, $type, $json->interface, $autoConnect, $ipv4, $ipv6, $wifi);
	}

	/**
	 * Serializes network connection entity into JSON
	 * @return array<string, string|array> JSON serialized data
	 */
	public function jsonSerialize(): array {
		$json = [
			'name' => $this->name,
			'uuid' => $this->uuid->toString(),
			'type' => (string) $this->type->toScalar(),
			'interface' => $this->interfaceName,
			'autoConnect' => $this->autoConnect->jsonSerialize(),
			'ipv4' => $this->ipv4->jsonSerialize(),
			'ipv6' => $this->ipv6->jsonSerialize(),
		];
		if ($this->wifi !== null) {
			$json['wifi'] = $this->wifi->jsonSerialize();
		}
		return $json;
	}

	/**
	 * Deserializes network connection entity from nmcli configuration
	 * @param string $nmCli nmcli connection configuration
	 * @return ConnectionDetail Detailed network connection entity
	 */
	public static function nmCliDeserialize(string $nmCli): INetworkManagerEntity {
		$array = NmCliConnection::decode($nmCli, self::NMCLI_PREFIX);
		if ($array['interface-name'] === '') {
			$general = NmCliConnection::decode($nmCli, 'GENERAL');
			if ($general !== []) {
				$array['interface-name'] = $general['devices'] ?? '';
			}
		}
		$autoConnect = AutoConnect::nmCliDeserialize($nmCli);
		$uuid = Uuid::fromString($array['uuid']);
		$type = ConnectionTypes::fromScalar($array['type']);
		$ipv4 = IPv4Connection::nmCliDeserialize($nmCli);
		$ipv6 = IPv6Connection::nmCliDeserialize($nmCli);
		$wifi = $type === ConnectionTypes::WIFI() ? WifiConnection::nmCliDeserialize($nmCli) : null;
		return new self($array['id'], $uuid, $type, $array['interface-name'], $autoConnect, $ipv4, $ipv6, $wifi);
	}

	/**
	 * Serializes network connection entity into nmcli configuration
	 * @return string nmcli configuration
	 */
	public function nmCliSerialize(): string {
		$array = [
			'id' => $this->name,
			'type' => $this->type,
			'interface-name' => $this->interfaceName,
		];
		$nmcli = NmCliConnection::encode($array, self::NMCLI_PREFIX);
		$nmcli .= $this->autoConnect->nmCliSerialize();
		$nmcli .= $this->ipv4->nmCliSerialize();
		$nmcli .= $this->ipv6->nmCliSerialize();
		if ($this->type->equals(ConnectionTypes::WIFI())) {
			$nmcli .= $this->wifi->nmCliSerialize();
		}
		return $nmcli;
	}

}
