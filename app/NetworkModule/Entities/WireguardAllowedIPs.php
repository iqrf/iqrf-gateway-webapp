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
 * Wireguard peer allowed IP addresses
 */
final class WireguardAllowedIPs implements JsonSerializable {

	/**
	 * @var array<IPv4Address> $ipv4 Wireguard peer allowed IPv4 addresses
	 */
	private $ipv4;

	/**
	 * @var array<IPv6Address> $ipv6 Wireguard peer allowed IPv6 addresses
	 */
	private $ipv6;

	/**
	 * Constructor
	 * @param array<IPv4Address> $ipv4 Wireguard peer allowed IPv4 addresses
	 * @param array<IPv6Address> $ipv6 Wireguard peer allowed IPv6 addresses
	 */
	public function __construct(array $ipv4, array $ipv6) {
		$this->ipv4 = $ipv4;
		$this->ipv6 = $ipv6;
	}

	/**
	 * Deserializes Wireguard peer allowed IP addresses JSON into entity
	 * @param stdClass $json Wireguard peer allowed IP addresses JSON
	 * @return WireguardAllowedIPs Wireguard peer allowed IP addresses entity
	 */
	public static function jsonDeserialize(stdClass $json): self {
		$ipv4s = [];
		foreach ($json->ipv4 as $ipv4) {
			$ipv4s[] = IPv4Address::fromPrefix($ipv4->address . '/' . $ipv4->prefix);
		}
		$ipv6s = [];
		foreach ($json->ipv6 as $ipv6) {
			$ipv6s[] = IPv6Address::fromPrefix($ipv6->address . '/' . $ipv6->prefix);
		}
		return new self($ipv4s, $ipv6s);
	}

	/**
	 * Serializes Wireguard peer allowed IP addresses entity into JSON
	 * @return array<string, array<array<string, int|string>>> JSON serialized Wireguard peer allowed IP addresses entity
	 */
	public function jsonSerialize(): array {
		return [
			'ipv4' => array_map(function (IPv4Address $a): array {
				return [
					'address' => $a->getAddress()->getDotAddress(),
					'prefix' => $a->getPrefix(),
				];
			}, $this->ipv4),
			'ipv6' => array_map(function (IPv6Address $a): array {
				return [
					'address' => $a->getAddress()->getCompactedAddress(),
					'prefix' => $a->getPrefix(),
				];
			}, $this->ipv6),
		];
	}

}
