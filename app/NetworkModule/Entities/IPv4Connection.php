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

use App\NetworkModule\Enums\IPv4Methods;
use Darsyn\IP\Version\IPv4;
use Nette\Utils\ArrayHash;
use Nette\Utils\Strings;
use stdClass;

/**
 * IPv4 connection entity
 */
final class IPv4Connection {

	/**
	 * @var IPv4Methods Connection method
	 */
	private $method;

	/**
	 * @var IPv4Address[] IPv4 addresses
	 */
	private $addresses;

	/**
	 * @var IPv4|null IPv4 gateway address
	 */
	private $gateway;

	/**
	 * @var IPv4[] IPv4 addresses of DNS servers
	 */
	private $dns;

	/**
	 * IPv4 connection entity constructor
	 * @param IPv4Methods $method Connection method
	 * @param IPv4Address[] $addresses IPv4 addresses
	 * @param IPv4|null $gateway IPv4 gateway address
	 * @param IPv4[] $dns DNS servers
	 */
	public function __construct(IPv4Methods $method, array $addresses, ?IPv4 $gateway, array $dns) {
		$this->method = $method;
		$this->addresses = $addresses;
		$this->gateway = $gateway;
		$this->dns = $dns;
	}

	/**
	 * Sets the values from the network comnnection configuration form
	 * @param stdClass|ArrayHash $form Values from the network connection configuration form
	 */
	public function fromForm(stdClass $form): void {
		$this->method = IPv4Methods::fromScalar($form->method);
		$this->addresses = [];
		foreach ($form->addresses as $address) {
			if (($address->address !== '') && ($address->mask !== null)) {
				$this->addresses[] = IPv4Address::fromMask($address->address, $address->mask);
			}
		}
		$this->gateway = $form->gateway !== '' ? IPv4::factory($form->gateway) : null;
		$this->dns = [];
		foreach ($form->dns as $dns) {
			if ($dns->address !== '') {
				$this->dns[] = IPv4::factory($dns->address);
			}
		}
	}

	/**
	 * Creates a new IPv4 connection entity from nmcli connection configuration
	 * @param string $nmCli nmcli connection configuration
	 * @return IPv4Connection IPv4 connection entity
	 */
	public static function fromNmCli(string $nmCli): self {
		$array = explode(PHP_EOL, Strings::trim($nmCli));
		foreach ($array as $i => $row) {
			$temp = explode(':', $row, 2);
			if (Strings::startsWith($temp[0], 'ipv4.')) {
				$key = Strings::replace($temp[0], '~ipv4\.~', '');
				$array[$key] = $temp[1];
			}
			unset($array[$i]);
		}
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
		return new static($method, $addresses, $gateway, $dns);
	}

	/**
	 * Returns the IPv4 connection method
	 * @return IPv4Methods IPv4 connection method
	 */
	public function getMethod(): IPv4Methods {
		return $this->method;
	}

	/**
	 * Returns the IPv4 addresses
	 * @return IPv4Address[] IPv4 addresses
	 */
	public function getAddresses(): array {
		return $this->addresses;
	}

	/**
	 * Returns the IPv4 gateway address
	 * @return IPv4|null IPv4 gateway address
	 */
	public function getGateway(): ?IPv4 {
		return $this->gateway;
	}

	/**
	 * Returns the IPv4 addresses of DNS servers
	 * @return IPv4[] IPv4 addresses of DNS servers
	 */
	public function getDns(): array {
		return $this->dns;
	}

	/**
	 * Converts IPv4 connection entity to an array for the form
	 * @return array<string,mixed> Array for the array
	 */
	public function toForm(): array {
		return [
			'method' => $this->method->toScalar(),
			'addresses' => array_map(function (IPv4Address $a): array {
				return $a->toArray();
			}, $this->addresses),
			'gateway' => $this->gateway !== null ? $this->gateway->getDotAddress() : '',
			'dns' => array_map(function (IPv4 $a): array {
				return ['address' => $a->getDotAddress()];
			}, $this->dns),
		];
	}

	/**
	 * Converts IPv4 connection entity to nmcli configuration string
	 * @return string nmcli configuration
	 */
	public function toNmCli(): string {
		$array = [
			'ipv4.method' => $this->method->toScalar(),
			'ipv4.addresses' => implode(' ', array_map(function (IPv4Address $address): string {
				return $address->toString();
			}, $this->addresses)),
			'ipv4.gateway' => ($this->gateway !== null) ? $this->gateway->getDotAddress() : '',
			'ipv4.dns' => implode(' ', array_map(function (IPv4 $ipv4): string {
				return $ipv4->getDotAddress();
			}, $this->dns)),
		];
		$string = '';
		foreach ($array as $key => $value) {
			$string .= sprintf('%s "%s" ', $key, $value);
		}
		return $string;
	}

}
