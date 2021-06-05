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
use Darsyn\IP\Version\IPv6;
use JsonSerializable;

final class IPv6Current implements JsonSerializable {

	/**
	 * @var IPv6Methods Connection method
	 */
	private $method;

	/**
	 * @var array<IPv6Address> IPv6 addresses
	 */
	private $addresses;

	/**
	 * @var IPv6|null IPv6 gateway address
	 */
	private $gateway;

	/**
	 * @var array<IPv6> IPv6 addresses of DNS servers
	 */
	private $dns;

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
	 * Serializes current IPv6 configuration entity into JSON
	 * @return array<string, array<array>|int|string> JSON serialized entity
	 */
	public function jsonSerialize(): array {
		return [
			'method' => $this->method->toScalar(),
			'addresses' => array_map(function (IPv6Address $a): array {
				return $a->toArray();
			}, $this->addresses),
			'gateway' => $this->gateway !== null ? $this->gateway->getCompactedAddress() : null,
			'dns' => array_map(function (IPv6 $a): array {
				return ['address' => $a->getCompactedAddress()];
			}, $this->dns),
		];
	}

}
