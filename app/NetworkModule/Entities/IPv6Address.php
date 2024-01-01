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

use Darsyn\IP\Version\IPv6;

/**
 * IPv6 address entity
 */
class IPv6Address {

	/**
	 * IPv6 address entity constructor
	 * @param IPv6 $address IPv6 address
	 * @param int $prefix IPv6 prefix
	 */
	public function __construct(
		private readonly IPv6 $address,
		private readonly int $prefix,
	) {
	}

	/**
	 * Creates a new IPv6 address entity from the IPv6 address and prefix as a string
	 * @param string $addr IPv6 address with prefix as a string
	 * @return IPv6Address IPv6 address entity
	 */
	public static function fromPrefix(string $addr): self {
		$array = explode('/', trim($addr));
		$address = IPv6::factory($array[0]);
		$prefix = (int) $array[1];
		return new self($address, $prefix);
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
	 * Converts the IPv6 address entity to an array
	 * @return array{address: string, prefix: int} IPv6 address entity in the array
	 */
	public function toArray(): array {
		return [
			'address' => $this->address->getCompactedAddress(),
			'prefix' => $this->prefix,
		];
	}

	/**
	 * Converts the IPv6 address entity to a string
	 * @return string IPv6 address and prefix
	 */
	public function toString(): string {
		return $this->address->getCompactedAddress() . '/' . $this->prefix;
	}

}
