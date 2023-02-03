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

use App\NetworkModule\Enums\IPv4Methods;
use App\NetworkModule\Utils\NmCliConnection;
use Darsyn\IP\Version\IPv4;
use Nette\Utils\ArrayHash;
use stdClass;

/**
 * IPv4 connection entity
 */
final class IPv4Connection implements INetworkManagerEntity {

	/**
	 * @var string nmcli configuration prefix
	 */
	private const NMCLI_PREFIX = 'ipv4';

	/**
	 * @var IPv4Methods Connection method
	 */
	private IPv4Methods $method;

	/**
	 * @var array<IPv4Address> IPv4 addresses
	 */
	private array $addresses = [];

	/**
	 * @var IPv4|null IPv4 gateway address
	 */
	private ?IPv4 $gateway;

	/**
	 * @var array<IPv4> IPv4 addresses of DNS servers
	 */
	private array $dns = [];

	/**
	 * @var IPv4Current|null Current IPv4 configuration
	 */
	private ?IPv4Current $current;

	/**
	 * IPv4 connection entity constructor
	 * @param IPv4Methods $method Connection method
	 * @param array<IPv4Address> $addresses IPv4 addresses
	 * @param IPv4|null $gateway IPv4 gateway address
	 * @param array<IPv4> $dns DNS servers
	 * @param IPv4Current|null $current Current configuration
	 */
	public function __construct(IPv4Methods $method, array $addresses, ?IPv4 $gateway, array $dns, ?IPv4Current $current) {
		$this->method = $method;
		$this->addresses = $addresses;
		$this->gateway = $gateway;
		$this->dns = $dns;
		$this->current = $current;
	}

	/**
	 * Deserializes IPv4 connection entity from JSON
	 * @param stdClass|ArrayHash $json JSON serialized entity
	 * @return IPv4Connection IPv4 connection entity
	 */
	public static function jsonDeserialize(stdClass $json): INetworkManagerEntity {
		$method = IPv4Methods::fromScalar($json->method);
		$addresses = [];
		foreach ($json->addresses as $address) {
			if ($address->address !== '') {
				if (isset($address->prefix)) {
					$addresses[] = new IPv4Address(IPv4::factory($address->address), $address->prefix);
				} elseif (isset($address->mask)) {
					$addresses[] = IPv4Address::fromMask($address->address, $address->mask);
				}
			}
		}
		$gateway = $json->gateway !== '' && isset($json->gateway) ? IPv4::factory($json->gateway) : null;
		$dns = [];
		foreach ($json->dns as $dnsServer) {
			if ($dnsServer->address !== '') {
				$dns[] = IPv4::factory($dnsServer->address);
			}
		}
		return new self($method, $addresses, $gateway, $dns, null);
	}

	/**
	 * Serializes IPv4 connection entity into JSON
	 * @return array{method: string, addresses: array<array{address: string, prefix: int, mask: string}>, gateway: string|null, dns: array<array{address: string}>, current?: array{method: string, addresses: array<array{address: string, prefix: int, mask: string}>, gateway: string|null, dns: array<array{address: string}>}} JSON serialized entity
	 */
	public function jsonSerialize(): array {
		$array = [
			'method' => $this->method->toScalar(),
			'addresses' => array_map(static fn (IPv4Address $a): array => $a->toArray(), $this->addresses),
			'gateway' => $this->gateway !== null ? $this->gateway->getDotAddress() : null,
			'dns' => array_map(static fn (IPv4 $a): array => ['address' => $a->getDotAddress()], $this->dns),
		];
		if ($this->current !== null) {
			$array['current'] = $this->current->jsonSerialize();
		}
		return $array;
	}

	/**
	 * Deserializes IPv4 connection entity from nmcli connection configuration
	 * @param array<string, array<string, array<string>|string>> $nmCli nmcli connection configuration
	 * @return IPv4Connection IPv4 connection entity
	 */
	public static function nmCliDeserialize(array $nmCli): INetworkManagerEntity {
		$array = $nmCli[self::NMCLI_PREFIX];
		$method = IPv4Methods::fromScalar($array['method']);
		$addresses = [];
		if ($array['addresses'] !== '') {
			$addresses = array_map(static fn (string $address): IPv4Address => IPv4Address::fromPrefix($address), explode(',', $array['addresses']));
		}
		$gateway = $array['gateway'] !== '' ? IPv4::factory($array['gateway']) : null;
		$dns = [];
		if ($array['dns'] !== '') {
			$dns = array_map(static fn (string $address): IPv4 => IPv4::factory($address), explode(',', $array['dns']));
		}
		if (array_key_exists(IPv4Current::NMCLI_PREFIX, $nmCli) &&
			$method === IPv4Methods::AUTO()) {
			$current = IPv4Current::nmCliDeserialize($nmCli);
		}
		return new self($method, $addresses, $gateway, $dns, $current ?? null);
	}

	/**
	 * Serializes IPv4 connection entity into nmcli configuration string
	 * @return string nmcli configuration
	 */
	public function nmCliSerialize(): string {
		$array = [
			'method' => $this->method->toScalar(),
			'addresses' => implode(' ', array_map(static fn (IPv4Address $address): string => $address->toString(), $this->addresses)),
			'gateway' => ($this->gateway !== null) ? $this->gateway->getDotAddress() : '',
			'dns' => implode(' ', array_map(static fn (IPv4 $ipv4): string => $ipv4->getDotAddress(), $this->dns)),
		];
		return NmCliConnection::encode($array, self::NMCLI_PREFIX);
	}

}
