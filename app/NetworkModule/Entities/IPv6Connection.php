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
	 * nmcli configuration prefix
	 */
	private const NMCLI_PREFIX = 'ipv6';

	/**
	 * @var IPv6Methods Connection method
	 */
	private $method;

	/**
	 * @var array<IPv6Address> IPv6 addresses
	 */
	private $addresses;

	/**
	 * @var array<IPv6> IPv6 addresses of DNS servers
	 */
	private $dns;

	/**
	 * IPv6 connection entity constructor
	 * @param IPv6Methods $method IPv6 connection method
	 * @param array<IPv6Address> $addresses IPv6 addresses
	 * @param array<IPv6> $dns IPv6 addresses of DNS servers
	 */
	public function __construct(IPv6Methods $method, array $addresses, array $dns) {
		$this->method = $method;
		$this->addresses = $addresses;
		$this->dns = $dns;
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
				$gateway = ($value->gateway !== '') ? IPv6::factory($value->gateway) : null;
				$addresses[] = new IPv6Address($address, $value->prefix, $gateway);
			}
		}
		$dns = [];
		foreach ($json->dns as $dnsServer) {
			if ($dnsServer->address !== '') {
				$dns[] = IPv6::factory($dnsServer->address);
			}
		}
		return new self($method, $addresses, $dns);
	}

	/**
	 * Serializes IPv6 connection entity into JSON
	 * @return array<string, array<array<string, int|string>>|array<string, string>|string> JSON serialized entity
	 */
	public function jsonSerialize(): array {
		return [
			'method' => $this->method->toScalar(),
			'addresses' => array_map(function (IPv6Address $a): array {
				return $a->toArray();
			}, $this->addresses),
			'dns' => array_map(function (IPv6 $a): array {
				return ['address' => $a->getCompactedAddress()];
			}, $this->dns),
		];
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
		$gateways = [];
		if ($array['gateway'] !== '') {
			$gateways = explode(',', $array['gateway']);
		}
		if ($array['addresses'] !== '') {
			foreach (explode(',', $array['addresses']) as $index => $address) {
				$gateway = $gateways[$index] ?? null;
				$addresses[] = IPv6Address::fromPrefix($address, $gateway);
			}
		}
		$dns = [];
		if ($array['dns'] !== '') {
			foreach (explode(',', $array['dns']) as $server) {
				$dns[] = IPv6::factory($server);
			}
		}
		return new self($method, $addresses, $dns);
	}

	/**
	 * Serializes IPv6 connection entity into nmcli configuration string
	 * @return string nmcli configuration
	 */
	public function nmCliSerialize(): string {
		$array = [
			'method' => $this->method->toScalar(),
			'addresses' => implode(' ', array_map(function (IPv6Address $address): string {
				return $address->toString();
			}, $this->addresses)),
			'gateway' => implode(' ', array_map(function (IPv6Address $address): string {
				return $address->getGateway()->getCompactedAddress();
			}, $this->addresses)),
			'dns' => implode(' ', array_map(function (IPv6 $ipv6): string {
				return $ipv6->getCompactedAddress();
			}, $this->dns)),
		];
		return NmCliConnection::encode($array, self::NMCLI_PREFIX);
	}

}
