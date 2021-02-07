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

use JsonSerializable;
use stdClass;

/**
 * Wireguard tunnel configuration
 */
final class WireguardTunnel implements JsonSerializable {

	/**
	 * @var string $name Wireguard tunnel name
	 */
	private $name;

	/**
	 * @var string $privateKey Wireguard tunnel interface private key
	 */
	private $privateKey;

	/**
	 * @var string $publicKey Wireguard tunnel interface public key
	 */
	private $publicKey;

	/**
	 * @var int $port Wireguard tunnel interface listen port
	 */
	private $port;

	/**
	 * @var IPv4Address $ipv4 Wireguard tunnel interface IPv4 address
	 */
	private $ipv4;

	/**
	 * @var IPv6Address $ipv6 Wireguard tunnel interface IPv6 address
	 */
	private $ipv6;

	/**
	 * @var array<WireguardPeer> $peers Array of Wireguard tunnel peers
	 */
	private $peers;

	/**
	 * Constructor
	 * @param string $name Wireguard tunnel interface name
	 * @param string $privateKey Wireguard tunnel interface private key
	 * @param string $publicKey Wireguard tunnel interface public key
	 * @param int $port Wireguard tunnel interface listen port
	 * @param IPv4Address $ipv4 Wireguard tunnel interface IPv4 address
	 * @param IPv6Address $ipv6 Wireguard tunnel interface IPv6 address
	 * @param array<WireguardPeer> $peers Wireguard tunnel interface peers
	 */
	public function __construct(string $name, string $privateKey, string $publicKey, int $port, IPv4Address $ipv4, IPv6Address $ipv6, array $peers) {
		$this->name = $name;
		$this->privateKey = $privateKey;
		$this->publicKey = $publicKey;
		$this->port = $port;
		$this->ipv4 = $ipv4;
		$this->ipv6 = $ipv6;
		$this->peers = $peers;
	}

	/**
	 * Deserializes Wireguard tunnel configuration JSON into entity
	 * @param stdClass $json Wireguard tunnel configuration JSON object
	 * @return WireguardTunnel Wireguard tunnel entity
	 */
	public static function jsonDeserialize(stdClass $json): self {
		$ipv4 = IPv4Address::fromPrefix($json->ipv4 . '/' . $json->ipv4Prefix);
		$ipv6 = IPv6Address::fromPrefix($json->ipv6 . '/' . $json->ipv6Prefix);
		$peers = [];
		foreach ($json->peers as $peer) {
			$peers[] = WireguardPeer::jsonDeserialize($peer);
		}
		return new self($json->name, $json->privateKey, $json->publicKey, $json->port, $ipv4, $ipv6, $peers);
	}

	/**
	 * Serializes Wireguard tunnel configuration entity into JSON
	 * @return array<string, array<array<string, array<string, array<array<string, int|string>>>|int|string|null>>|int|string> JSON serialized Wireguard tunnel configuration entity
	 */
	public function jsonSerialize(): array {
		return [
			'name' => $this->name,
			'privateKey' => $this->privateKey,
			'publicKey' => $this->publicKey,
			'port' => $this->port,
			'ipv4' => $this->ipv4->getAddress()->getDotAddress(),
			'ipv4Prefix' => $this->ipv4->getPrefix(),
			'ipv6' => $this->ipv6->getAddress()->getCompactedAddress(),
			'ipv6Prefix' => $this->ipv6->getPrefix(),
			'peers' => array_map(function (WireguardPeer $p): array {
				return $p->jsonSerialize();
			}, $this->peers),
		];
	}

}
