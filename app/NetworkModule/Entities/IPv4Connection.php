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
	 * nmcli configuration prefix
	 */
	private const NMCLI_PREFIX = 'ipv4';

	/**
	 * nmcli current configuration prefix
	 */
	private const NMCLI_CURRENT_PREFIX = 'IP4';

	/**
	 * @var IPv4Methods Connection method
	 */
	private IPv4Methods $method;

	/**
	 * @var array<IPv4Address> IPv4 addresses
	 */
	private array $addresses;

	/**
	 * @var IPv4|null IPv4 gateway address
	 */
	private ?IPv4 $gateway;

	/**
	 * @var array<IPv4> IPv4 addresses of DNS servers
	 */
	private array $dns;

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
			if (($address->address !== '')) {
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
	 * @return array<string, array<string, string>|array<int, array<string, int|string>>|int|string|null> JSON serialized entity
	 */
	public function jsonSerialize(): array {
		$array = [
			'method' => $this->method->toScalar(),
			'addresses' => array_map(function (IPv4Address $a): array {
				return $a->toArray();
			}, $this->addresses),
			'gateway' => $this->gateway !== null ? $this->gateway->getDotAddress() : null,
			'dns' => array_map(function (IPv4 $a): array {
				return ['address' => $a->getDotAddress()];
			}, $this->dns),
		];
		if ($this->current !== null) {
			$array['current'] = $this->current->jsonSerialize();
		}
		return $array;
	}

	/**
	 * Deserializes IPv4 connection entity from nmcli connection configuration
	 * @param string $nmCli nmcli connection configuration
	 * @return IPv4Connection IPv4 connection entity
	 */
	public static function nmCliDeserialize(string $nmCli): INetworkManagerEntity {
		$array = NmCliConnection::decode($nmCli, self::NMCLI_PREFIX);
		$method = IPv4Methods::fromScalar($array['method']);
		$addresses = [];
		if ($array['addresses'] !== '') {
			foreach (explode(',', $array['addresses']) as $address) {
				$addresses[] = IPv4Address::fromPrefix($address);
			}
		}
		$gateway = $array['gateway'] !== '' ? IPv4::factory($array['gateway']) : null;
		$dns = [];
		if ($array['dns'] !== '') {
			foreach (explode(',', $array['dns']) as $server) {
				$dns[] = IPv4::factory($server);
			}
		}
		if ($method === IPv4Methods::AUTO()) {
			$config = NmCliConnection::decode($nmCli, self::NMCLI_CURRENT_PREFIX);
			$currentAddresses = [];
			if (array_key_exists('address', $config)) {
				foreach ($config['address'] as $addr) {
					$currentAddresses[] = IPv4Address::fromPrefix($addr);
				}
			}
			$currentGateway = array_key_exists('gateway', $config) ? IPv4::factory($config['gateway']) : null;
			$currentDns = [];
			if (array_key_exists('dns', $config)) {
				foreach ($config['dns'] as $addr) {
					$currentDns[] = IPv4::factory($addr);
				}
			}
			$current = new IPv4Current($currentAddresses, $currentGateway, $currentDns);
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
			'addresses' => implode(' ', array_map(function (IPv4Address $address): string {
				return $address->toString();
			}, $this->addresses)),
			'gateway' => ($this->gateway !== null) ? $this->gateway->getDotAddress() : '',
			'dns' => implode(' ', array_map(function (IPv4 $ipv4): string {
				return $ipv4->getDotAddress();
			}, $this->dns)),
		];
		return NmCliConnection::encode($array, self::NMCLI_PREFIX);
	}

}
