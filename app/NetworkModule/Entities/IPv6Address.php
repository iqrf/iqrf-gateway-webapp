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

use Darsyn\IP\Version\IPv6;

/**
 * IPv6 address entity
 */
class IPv6Address {

	/**
	 * @var IPv6 IPv6 address
	 */
	private $address;

	/**
	 * @var int IPv6 prefix
	 */
	private $prefix;

	/**
	 * @var IPv6|null IPv6 gateway address
	 */
	private $gateway;

	/**
	 * IPv6 address entity constructor
	 * @param IPv6 $address IPv6 address
	 * @param int $prefix IPv6 prefix
	 * @param IPv6|null $gateway IPv6 gateway address
	 */
	public function __construct(IPv6 $address, int $prefix, ?IPv6 $gateway = null) {
		$this->address = $address;
		$this->prefix = $prefix;
		$this->gateway = $gateway;
	}

	/**
	 * Creates a new IPv6 address entity from the IPv6 address and prefix as a string
	 * @param string $addr IPv6 address with prefix as a string
	 * @param string|null $gwAddr IPv6 gateway address
	 * @return IPv6Address IPv6 address entity
	 */
	public static function fromPrefix(string $addr, ?string $gwAddr = null): self {
		$array = explode('/', trim($addr));
		$address = IPv6::factory($array[0]);
		$prefix = intval($array[1]);
		$gateway = ($gwAddr !== null) ? IPv6::factory($gwAddr) : null;
		return new self($address, $prefix, $gateway);
	}

	/**
	 * Returns the IPv6 address
	 * @return IPv6 IPv6 address
	 */
	public function getAddress(): IPv6 {
		return $this->address;
	}

	/**
	 * Returns the IPv6 prefix
	 * @return int IPv6 prefix
	 */
	public function getPrefix(): int {
		return $this->prefix;
	}

	/**
	 * Returns the IPv6 gateway prefix
	 * @return IPv6|null IPv6 gateway address
	 */
	public function getGateway(): ?IPv6 {
		return $this->gateway;
	}

	/**
	 * Converts the IPv6 address entity to an array
	 * @return array<string,mixed> IPv6 address entity in the array
	 */
	public function toArray(): array {
		$array = [
			'address' => $this->address->getCompactedAddress(),
			'prefix' => $this->prefix,
			'gateway' => '',
		];
		if ($this->gateway !== null) {
			$array['gateway'] = $this->gateway->getCompactedAddress();
		}
		return $array;
	}

	/**
	 * Converts the IPv6 address entity to a string
	 * @return string IPv6 address and prefix
	 */
	public function toString(): string {
		return $this->address->getCompactedAddress() . '/' . $this->prefix;
	}

}
