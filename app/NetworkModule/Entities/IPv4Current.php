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

use Darsyn\IP\Version\IPv4;
use JsonSerializable;

/**
 * Current configuration entity
 */
final class IPv4Current implements JsonSerializable {

	/**
	 * nmcli current configuration prefix
	 */
	public const NMCLI_PREFIX = 'IP4';

	/**
	 * Current IPv4 configuration entity
	 * @param array<IPv4Address> $addresses IPv4 addresses
	 * @param IPv4|null $gateway IPv4 gateway address
	 * @param array<IPv4> $dns DNS servers
	 */
	public function __construct(
		private readonly array $addresses,
		private readonly ?IPv4 $gateway,
		private readonly array $dns,
	) {
	}

	/**
	 * Deserializes IPv4 current configuration from nmcli output
	 * @param array<string, array<string, array<string>|string>> $nmCli nmcli connection configuration
	 * @return static IPv4 current configuration
	 */
	public static function nmCliDeserialize(array $nmCli): self {
		$array = $nmCli[self::NMCLI_PREFIX] ?? [];
		if (array_key_exists('ADDRESS', $array)) {
			$addresses = array_map(static fn (string $address): IPv4Address => IPv4Address::fromPrefix($address), $array['ADDRESS']);
		}
		$gateway = array_key_exists('GATEWAY', $array) ? IPv4::factory($array['GATEWAY']) : null;
		if (array_key_exists('DNS', $array)) {
			$dns = array_map(static fn (string $address): IPv4 => IPv4::factory($address), $array['DNS']);
		}
		return new self($addresses ?? [], $gateway, $dns ?? []);
	}

	/**
	 * Serializes current IPv4 configuration entity to JSON
	 * @return array{method: string, addresses: array<array{address: string, prefix: int, mask: string}>, gateway: string|null, dns: array<array{address: string}>} IPv4 current configuration
	 */
	public function jsonSerialize(): array {
		return [
			'method' => 'auto',
			'addresses' => array_map(static fn (IPv4Address $a): array => $a->toArray(), $this->addresses),
			'gateway' => $this->gateway?->getDotAddress(),
			'dns' => array_map(static fn (IPv4 $a): array => ['address' => $a->getDotAddress()], $this->dns),
		];
	}

}
