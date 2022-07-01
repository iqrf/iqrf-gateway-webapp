<?php

/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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
use Darsyn\IP\Version\IPv6;
use Nette\Utils\ArrayHash;
use stdClass;

/**
 * IPv6 connection entity
 */
final class IPv6Connection implements INetworkManagerEntity {

	/**
	 * @var string nmcli configuration prefix
	 */
	private const NMCLI_PREFIX = 'ipv6';

	/**
	 * @var string nmcli current configuration prefix
	 */
	private const NMCLI_CURRENT_PREFIX = 'IP6';

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
	 * @var IPv6Current|null Current IPv6 configuration
	 */
	private ?IPv6Current $current;

	/**
	 * IPv6 connection entity constructor
	 * @param IPv6Methods $method IPv6 connection method
	 * @param array<IPv6Address> $addresses IPv6 addresses
	 * @param IPv6|null $gateway IPv4 gateway address
	 * @param array<IPv6> $dns IPv6 addresses of DNS servers
	 * @param IPv6Current|null $current Current IPv6 configuration
	 */
	public function __construct(IPv6Methods $method, array $addresses, ?IPv6 $gateway, array $dns, ?IPv6Current $current) {
		$this->method = $method;
		$this->addresses = $addresses;
		$this->gateway = $gateway;
		$this->dns = $dns;
		$this->current = $current;
	}

	/**
	 * Deserializes IPv6 connection entity from JSON
	 * @param stdClass|ArrayHash $json JSON serialized entity
	 * @return IPv6Connection IPv6 connection entity
	 */
	public static function jsonDeserialize(stdClass $json): INetworkManagerEntity {
		$method = IPv6Methods::fromScalar($json->method);
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
	 * Serializes IPv6 connection entity into JSON
	 * @return array{method: string, addresses: array<array{address: string, prefix: int}>, gateway: string|null, dns: array<array{address: string}>, current?: array{method: string, addresses: array<array{address: string, prefix: int}>, gateway: string|null, dns: array<array{address: string}>}} JSON serialized entity
	 */
	public function jsonSerialize(): array {
		$array = [
			'method' => $this->method->toScalar(),
			'addresses' => array_map(fn (IPv6Address $a): array => $a->toArray(), $this->addresses),
			'gateway' => $this->gateway !== null ? $this->gateway->getCompactedAddress() : null,
			'dns' => array_map(fn (IPv6 $a): array => ['address' => $a->getCompactedAddress()], $this->dns),
		];
		if ($this->current !== null) {
			$array['current'] = $this->current->jsonSerialize();
		}
		return $array;
	}

	/**
	 * Deserializes IPv6 connection entity from nmcli connection configuration
	 * @param string $nmCli nmcli connection configuration
	 * @return IPv6Connection IPv6 connection entity
	 */
	public static function nmCliDeserialize(string $nmCli): INetworkManagerEntity {
		$array = NmCliConnection::decode($nmCli, self::NMCLI_PREFIX);
		$method = IPv6Methods::fromScalar($array['method']);
		$addresses = [];
		$gateway = array_key_exists('gateway', $array) && ($array['gateway'] !== '') ? IPv6::factory($array['gateway']) : null;
		if ($array['addresses'] !== '') {
			$addresses = array_map(fn(string $address) => IPv6Address::fromPrefix($address), explode(',', $array['addresses']));
		}
		$dns = [];
		if ($array['dns'] !== '') {
			$dns = array_map(fn (string $address): IPv6 => IPv6::factory($address), explode(',', $array['dns']));
		}
		if ($method === IPv6Methods::AUTO() || $method === IPv6Methods::DHCP()) {
			$config = NmCliConnection::decode($nmCli, self::NMCLI_CURRENT_PREFIX);
			$currentAddresses = [];
			$currentGateway = array_key_exists('gateway', $config) && ($config['gateway'] !== '') ? IPv6::factory($config['gateway']) : null;
			if (array_key_exists('address', $config)) {
				$currentAddresses = array_map(fn(string $address): IPv6Address => IPv6Address::fromPrefix($address), array_values($config['address']));
			}
			$currentDns = [];
			if (array_key_exists('dns', $config)) {
				$currentDns = array_map(fn(string $address): IPv6 => IPv6::factory($address), array_values($config['dns']));
			}
			$current = new IPv6Current($method, $currentAddresses, $currentGateway, $currentDns);
		}
		return new self($method, $addresses, $gateway, $dns, $current ?? null);
	}

	/**
	 * Serializes IPv6 connection entity into nmcli configuration string
	 * @return string nmcli configuration
	 */
	public function nmCliSerialize(): string {
		$array = [
			'method' => $this->method->toScalar(),
			'addresses' => implode(', ', array_map(fn (IPv6Address $address): string => $address->toString(), $this->addresses)),
			'gateway' => ($this->gateway !== null) ? $this->gateway->getCompactedAddress() : '',
			'dns' => implode(' ', array_map(fn (IPv6 $ipv6): string => $ipv6->getCompactedAddress(), $this->dns)),
		];
		return NmCliConnection::encode($array, self::NMCLI_PREFIX);
	}

}
