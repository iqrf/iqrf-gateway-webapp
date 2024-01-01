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

use App\NetworkModule\Enums\ConnectionTypes;
use App\NetworkModule\Utils\NmCliConnection;
use Nette\Utils\ArrayHash;
use Nette\Utils\Strings;
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
	final public const NMCLI_PREFIX = 'connection';

	/**
	 * @var WifiConnection|null WiFi network connection entity
	 */
	private ?WifiConnection $wifi = null;

	/**
	 * @var GSMConnection|null GSM network connection entity
	 */
	private ?GSMConnection $gsm = null;

	/**
	 * @var SerialLink|null Serial link entity
	 */
	private ?SerialLink $serial = null;

	/**
	 * Network connection entity constructor
	 * @param string $name Network connection name
	 * @param UuidInterface $uuid Network connection UUID
	 * @param ConnectionTypes $type Network connection type
	 * @param string $interfaceName Network interface name
	 * @param AutoConnect $autoConnect Automatic connection entity
	 * @param IPv4Connection $ipv4 IPv4 network connection entity
	 * @param IPv6Connection $ipv6 IPv6 network connection entity
	 */
	public function __construct(
		private readonly string $name,
		private readonly UuidInterface $uuid,
		private readonly ConnectionTypes $type,
		private readonly string $interfaceName,
		private readonly AutoConnect $autoConnect,
		private readonly IPv4Connection $ipv4,
		private readonly IPv6Connection $ipv6,
	) {
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
		$type = ConnectionTypes::from($json->type);
		$connection = new self($json->name, $uuid, $type, $json->interface, $autoConnect, $ipv4, $ipv6);
		switch ($type) {
			case ConnectionTypes::WIFI:
				$connection->setWifi(WifiConnection::jsonDeserialize($json->wifi));
				break;
			case ConnectionTypes::GSM:
				$connection->setGsm(GSMConnection::jsonDeserialize($json->gsm));
				if (Strings::match($json->interface, '~^tty(AMA|ACM|S)\d+$~') !== null) {
					$connection->setSerial(SerialLink::jsonDeserialize($json->serial));
				}
				break;
		}
		return $connection;
	}

	/**
	 * Deserializes network connection entity from nmcli configuration
	 * @param array<string, array<string, array<string>|string>> $nmCli nmcli connection configuration
	 * @return ConnectionDetail Detailed network connection entity
	 */
	public static function nmCliDeserialize(array $nmCli): INetworkManagerEntity {
		$array = $nmCli[self::NMCLI_PREFIX];
		$interface = $array['interface-name'] ?? '';
		if ($interface === '') {
			$general = $nmCli['GENERAL'];
			if ($general !== []) {
				$interface = $general['devices'] ?? '';
			}
		}
		$name = $array['id'] ?? '';
		$autoConnect = AutoConnect::nmCliDeserialize($nmCli);
		$uuid = Uuid::fromString($array['uuid']);
		$type = ConnectionTypes::tryFrom($array['type']);
		$interface = $array['interface-name'];
		$ipv4 = IPv4Connection::nmCliDeserialize($nmCli);
		$ipv6 = IPv6Connection::nmCliDeserialize($nmCli);
		$connection = new self($name, $uuid, $type, $interface, $autoConnect, $ipv4, $ipv6);
		switch ($type) {
			case ConnectionTypes::WIFI:
				$connection->setWifi(WifiConnection::nmCliDeserialize($nmCli));
				break;
			case ConnectionTypes::GSM:
				$connection->setGsm(GSMConnection::nmCliDeserialize($nmCli));
				if (Strings::match($interface, '~^tty(AMA|ACM|S)\d+$~') !== null &&
					array_key_exists(SerialLink::NMCLI_PREFIX, $nmCli)) {
					$connection->setSerial(SerialLink::nmCliDeserialize($nmCli));
				}
				break;
		}
		return $connection;
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
	 * Sets the GSM connection entity
	 * @param GSMConnection|null $gsm GSM connection entity
	 */
	public function setGsm(?GSMConnection $gsm): void {
		$this->gsm = $gsm;
	}

	/**
	 * Sets the Serial link entity
	 * @param SerialLink|null $serial Serial link entity
	 */
	public function setSerial(?SerialLink $serial): void {
		$this->serial = $serial;
	}

	/**
	 * Sets the Wi-Fi connection entity
	 * @param WifiConnection|null $wifi Wi-Fi connection entity
	 */
	public function setWifi(?WifiConnection $wifi): void {
		$this->wifi = $wifi;
	}

	/**
	 * Serializes network connection entity into JSON
	 * @return array<string, array<string, array<array<array<int|string>|int|string>|string|null>|bool|int|string|null>|string> JSON serialized data
	 */
	public function jsonSerialize(): array {
		$json = [
			'name' => $this->name,
			'uuid' => $this->uuid->toString(),
			'type' => $this->type->value,
			'interface' => $this->interfaceName,
			'autoConnect' => $this->autoConnect->jsonSerialize(),
			'ipv4' => $this->ipv4->jsonSerialize(),
			'ipv6' => $this->ipv6->jsonSerialize(),
		];
		if ($this->wifi !== null) {
			$json['wifi'] = $this->wifi->jsonSerialize();
		}
		if ($this->gsm !== null) {
			$json['gsm'] = $this->gsm->jsonSerialize();
		}
		if ($this->serial !== null) {
			$json['serial'] = $this->serial->jsonSerialize();
		}
		return $json;
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
		switch ($this->type) {
			case ConnectionTypes::WIFI:
				$nmcli .= $this->wifi->nmCliSerialize();
				break;
			case ConnectionTypes::GSM:
				$nmcli .= $this->gsm->nmCliSerialize();
				if ($this->serial !== null) {
					$nmcli .= $this->serial->nmCliSerialize();
				}
				break;
		}
		return $nmcli;
	}

}
