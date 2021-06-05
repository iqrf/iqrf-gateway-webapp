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

use Darsyn\IP\Version\IPv4;
use JsonSerializable;

/**
 * Current configuration entity
 */
final class IPv4Current implements JsonSerializable {

	/**
	 * @var array<IPv4Address> IPv4 addresses
	 */
	private $addresses;

	/**
	 * @var IPv4|null IPv4 gateway address
	 */
	private $gateway;

	/**
	 * @var array<IPv4> IPv4 addresses of DNS servers
	 */
	private $dns;

	/**
	 * Current IPv4 configuration entity
	 * @param array<IPv4Address> $addresses IPv4 addresses
	 * @param IPv4|null $gateway IPv4 gateway address
	 * @param array<IPv4> $dns DNS servers
	 */
	public function __construct(array $addresses, ?IPv4 $gateway, array $dns) {
		$this->addresses = $addresses;
		$this->gateway = $gateway;
		$this->dns = $dns;
	}

	/**
	 * Serializes current IPv4 configuration entity to JSON
	 * @return array<string, array<array<string, int|string>>|string|null>
	 */
	public function jsonSerialize(): array {
		return [
			'method' => 'auto',
			'addresses' => array_map(function (IPv4Address $a): array {
				return $a->toArray();
			}, $this->addresses),
			'gateway' => $this->gateway !== null ? $this->gateway->getDotAddress() : null,
			'dns' => array_map(function (IPv4 $a): array {
				return ['address' => $a->getDotAddress()];
			}, $this->dns),
		];
	}

}
