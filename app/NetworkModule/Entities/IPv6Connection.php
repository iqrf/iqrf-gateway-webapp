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
use Darsyn\IP\Version\IPv6;
use Nette\Utils\ArrayHash;
use Nette\Utils\Strings;
use stdClass;

/**
 * IPv6 connection entity
 */
final class IPv6Connection {

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
	 * Sets the value from the network connection configuration form
	 * @param stdClass|ArrayHash $form Values from the network connection configuration form
	 */
	public function fromForm(stdClass $form): void {
		$this->method = IPv6Methods::fromScalar($form->method);
		$this->addresses = [];
		foreach ($form->addresses as $value) {
			if (($value->address !== '') && ($value->prefix !== null)) {
				$address = IPv6::factory($value->address);
				$gateway = ($value->gateway !== '') ? IPv6::factory($value->gateway) : null;
				$this->addresses[] = new IPv6Address($address, $value->prefix, $gateway);
			}
		}
		$this->dns = [];
		foreach ($form->dns as $dns) {
			if ($dns->address !== '') {
				$this->dns[] = IPv6::factory($dns->address);
			}
		}
	}

	/**
	 * Creates a new IPv6 connection entity from nmcli connection configuration
	 * @param string $nmCli nmcli connection configuration
	 * @return IPv6Connection IPv6 connection entity
	 */
	public static function fromNmCli(string $nmCli): self {
		$array = explode(PHP_EOL, Strings::trim($nmCli));
		foreach ($array as $i => $row) {
			$temp = explode(':', $row, 2);
			if (Strings::startsWith($temp[0], 'ipv6.')) {
				$key = Strings::replace($temp[0], '~ipv6\.~', '');
				$array[$key] = $temp[1];
			}
			unset($array[$i]);
		}
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
		return new static($method, $addresses, $dns);
	}

	/**
	 * Returns the IPv6 connection method
	 * @return IPv6Methods IPv6 connection method
	 */
	public function getMethod(): IPv6Methods {
		return $this->method;
	}

	/**
	 * Returns the IPv6 addresses
	 * @return array<IPv6Address> IPv6 addresses
	 */
	public function getAddresses(): array {
		return $this->addresses;
	}
	/**
	 * Returns the IPv6 addresses of DNS servers
	 * @return array<IPv6> IPv6 addresses of DNS servers
	 */
	public function getDns(): array {
		return $this->dns;
	}

	/**
	 * Converts IPv6 connection entity to an array for the form
	 * @return array<string, array<array<string, int|string>>|array<string, string>|string> Array for the form
	 */
	public function toForm(): array {
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
	 * Converts IPv6 connection entity to nmcli configuration string
	 * @return string nmcli configuration
	 */
	public function toNmCli(): string {
		$array = [
			'ipv6.method' => $this->method->toScalar(),
			'ipv6.addresses' => implode(' ', array_map(function (IPv6Address $address): string {
				return $address->toString();
			}, $this->addresses)),
			'ipv6.gateway' => implode(' ', array_map(function (IPv6Address $address): string {
				return $address->getGateway()->getCompactedAddress();
			}, $this->addresses)),
			'ipv6.dns' => implode(' ', array_map(function (IPv6 $ipv6): string {
				return $ipv6->getCompactedAddress();
			}, $this->dns)),
		];
		$string = '';
		foreach ($array as $key => $value) {
			$string .= sprintf('%s "%s" ', $key, $value);
		}
		return $string;
	}

}
