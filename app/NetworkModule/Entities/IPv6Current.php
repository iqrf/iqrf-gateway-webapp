<?php

/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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

use App\NetworkModule\Enums\IPv6Methods;
use Darsyn\IP\Version\IPv6;
use JsonSerializable;

final class IPv6Current implements JsonSerializable {

	/**
	 * @var string nmcli configuration prefix
	 */
	public const NMCLI_PREFIX = 'IP6';

	/**
	 * @var IPv6Methods Connection method
	 */
	private IPv6Methods $method;

	/**
	 * @var array<IPv6Address> IPv6 addresses
	 */
	private array $addresses = [];

	/**
	 * @var IPv6|null IPv6 gateway address
	 */
	private ?IPv6 $gateway;

	/**
	 * @var array<IPv6> IPv6 addresses of DNS servers
	 */
	private array $dns = [];

	/**
	 * IPv6 current configuration constructor
	 * @param IPv6Methods $method IPv6 connection method
	 * @param array<IPv6Address> $addresses IPv6 addresses
	 * @param IPv6|null $gateway IPv6 gateway address
	 * @param array<IPv6> $dns IPv6 addresses of DNS servers
	 */
	public function __construct(IPv6Methods $method, array $addresses, ?IPv6 $gateway, array $dns) {
		$this->method = $method;
		$this->addresses = $addresses;
		$this->gateway = $gateway;
		$this->dns = $dns;
	}

	/**
	 * Deserializes IPv6 current configuration from nmcli output
	 * @param array<string, array<string, array<string>|string>> $nmCli nmcli connection configuration
	 * @param IPv6Methods $method IPv6 connection method
	 * @return static IPv6 current configuration
	 */
	public static function nmCliDeserialize(array $nmCli, IPv6Methods $method): self {
		$array = $nmCli[self::NMCLI_PREFIX] ?? [];
		if (array_key_exists('GATEWAY', $array) && ($array['GATEWAY'] !== '')) {
			$gateway = IPv6::factory($array['GATEWAY']);
		} else {
			$gateway = null;
		}
		if (array_key_exists('ADDRESS', $array)) {
			$addresses = array_map(static fn(string $address): IPv6Address => IPv6Address::fromPrefix($address), $array['ADDRESS']);
		} else {
			$addresses = [];
		}
		$dns = [];
		if (array_key_exists('DNS', $array)) {
			$dns = array_map(static fn(string $address): IPv6 => IPv6::factory($address), $array['DNS']);
		}
		return new self($method, $addresses, $gateway, $dns);
	}

	/**
	 * Serializes current IPv6 configuration entity into JSON
	 * @return array{method: string, addresses: array<array{address: string, prefix: int}>, gateway: string|null, dns: array<array{address: string}>} JSON serialized entity
	 */
	public function jsonSerialize(): array {
		return [
			'method' => $this->method->toScalar(),
			'addresses' => array_map(static fn (IPv6Address $a): array => $a->toArray(), $this->addresses),
			'gateway' => $this->gateway !== null ? $this->gateway->getCompactedAddress() : null,
			'dns' => array_map(static fn (IPv6 $a): array => ['address' => $a->getCompactedAddress()], $this->dns),
		];
	}

}
