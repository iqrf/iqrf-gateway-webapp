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

use App\NetworkModule\Enums\IPv6Methods;
use App\NetworkModule\Utils\NmCliConnection;
use Darsyn\IP\Exception\InvalidIpAddressException;
use Darsyn\IP\Exception\WrongVersionException;
use Darsyn\IP\Version\IPv6;
use stdClass;

/**
 * IPv6 connection entity
 */
final readonly class IPv6Connection implements INetworkManagerEntity {

	/**
	 * nmcli configuration prefix
	 */
	private const NMCLI_PREFIX = 'ipv6';

	/**
	 * IPv6 connection entity constructor
	 * @param IPv6Methods $method IPv6 connection method
	 * @param array<IPv6Address> $addresses IPv6 addresses
	 * @param IPv6|null $gateway IPv4 gateway address
	 * @param array<IPv6> $dns IPv6 addresses of DNS servers
	 * @param IPv6Current|null $current Current IPv6 configuration
	 */
	public function __construct(
		private IPv6Methods $method,
		private array $addresses,
		private ?IPv6 $gateway,
		private array $dns,
		private ?IPv6Current $current,
	) {
	}

	/**
	 * Deserializes IPv6 connection entity from JSON
	 * @param stdClass $json JSON serialized entity
	 * @return IPv6Connection IPv6 connection entity
	 * @throws InvalidIpAddressException Invalid IP address
	 * @throws WrongVersionException Invalid IP address version
	 */
	public static function jsonDeserialize(stdClass $json): INetworkManagerEntity {
		$method = IPv6Methods::from($json->method);
		$addresses = [];
		foreach ($json->addresses as $value) {
			if (($value->address !== '') && ($value->prefix !== null)) {
				$address = IPv6::factory($value->address);
				$addresses[] = new IPv6Address($address, $value->prefix);
			}
		}
		$gateway = $json->gateway !== '' && isset($json->gateway) ? IPv6::factory($json->gateway) : null;
		$dns = [];
		foreach ($json->dns as $dnsServer) {
			if ($dnsServer->address !== '') {
				$dns[] = IPv6::factory($dnsServer->address);
			}
		}
		return new self($method, $addresses, $gateway, $dns, null);
	}

	/**
	 * Deserializes IPv6 connection entity from nmcli connection configuration
	 * @param array<string, array<string, array<string>|string>> $nmCli nmcli connection configuration
	 * @return IPv6Connection IPv6 connection entity
	 */
	public static function nmCliDeserialize(array $nmCli): INetworkManagerEntity {
		$array = $nmCli[self::NMCLI_PREFIX];
		$method = IPv6Methods::from($array['method']);
		$addresses = [];
		$gateway = array_key_exists('gateway', $array) && ($array['gateway'] !== '') ? IPv6::factory($array['gateway']) : null;
		if ($array['addresses'] !== '') {
			$addresses = array_map(static fn (string $address): IPv6Address => IPv6Address::fromPrefix($address), explode(',', $array['addresses']));
		}
		$dns = [];
		if ($array['dns'] !== '') {
			$dns = array_map(static fn (string $address): IPv6 => IPv6::factory($address), explode(',', $array['dns']));
		}
		if (array_key_exists(IPv6Current::NMCLI_PREFIX, $nmCli) &&
			($method === IPv6Methods::AUTO || $method === IPv6Methods::DHCP)) {
			$current = IPv6Current::nmCliDeserialize($nmCli, $method);
		}
		return new self($method, $addresses, $gateway, $dns, $current ?? null);
	}

	/**
	 * Serializes IPv6 connection entity into JSON
	 * @return array{
	 *     method: string,
	 *     addresses: array<array{
	 *         address: string,
	 *         prefix: int,
	 *     }>,
	 *     gateway: string|null,
	 *     dns: array<array{address: string}>,
	 *     current?: array{
	 *         method: string,
	 *         addresses: array<array{
	 *             address: string,
	 *             prefix: int,
	 *         }>,
	 *         gateway: string|null,
	 *         dns: array<array{address: string}>,
	 *     },
	 * } JSON serialized entity
	 */
	public function jsonSerialize(): array {
		$array = [
			'method' => $this->method->value,
			'addresses' => array_map(static fn (IPv6Address $a): array => $a->toArray(), $this->addresses),
			'gateway' => $this->gateway?->getCompactedAddress(),
			'dns' => array_map(static fn (IPv6 $a): array => ['address' => $a->getCompactedAddress()], $this->dns),
		];
		if ($this->current instanceof IPv6Current) {
			$array['current'] = $this->current->jsonSerialize();
		}
		return $array;
	}

	/**
	 * Serializes IPv6 connection entity into nmcli configuration string
	 * @return string nmcli configuration
	 */
	public function nmCliSerialize(): string {
		$array = [
			'method' => $this->method->value,
			'addresses' => implode(', ', array_map(static fn (IPv6Address $address): string => $address->toString(), $this->addresses)),
			'gateway' => ($this->gateway instanceof IPv6) ? $this->gateway->getCompactedAddress() : '',
			'dns' => implode(' ', array_map(static fn (IPv6 $ipv6): string => $ipv6->getCompactedAddress(), $this->dns)),
		];
		return NmCliConnection::encode($array, self::NMCLI_PREFIX);
	}

}
