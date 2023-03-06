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

use Darsyn\IP\Version\IPv4;

/**
 * IPv4 address entity
 */
class IPv4Address {

	/**
	 * @var int IPv4 address prefix
	 */
	private readonly int $prefix;

	/**
	 * IPv4 address entity constructor
	 * @param IPv4 $address IPv4 address
	 * @param int $prefix IPv4 address prefix
	 */
	public function __construct(
		private readonly IPv4 $address,
		int $prefix,
	) {
		if ($prefix <= 32) {
			$this->prefix = $prefix;
		}
	}

	/**
	 * Creates a new IPv4 address entity from the address and the subnet mask
	 * @param string $address IPv4 address
	 * @param string $mask IPv4 subnet mask
	 * @return IPv4Address IPv4 address entity
	 */
	public static function fromMask(string $address, string $mask): self {
		$temp = ip2long($mask);
		$negative = $temp < 0;
		$prefix = 0;
		if ($negative) {
			$temp = $temp * (-1) - 1;
		}
		while ($temp !== 0) {
			$prefix += $temp & 1;
			$temp >>= 1;
		}
		if ($negative) {
			$prefix = 32 - $prefix;
		}
		return new self(IPv4::factory($address), $prefix);
	}

	/**
	 * Creates a new IPv4 address entity from the address with prefix
	 * @param string $string IPv4 address with prefix
	 * @return IPv4Address IPv4 address entity
	 */
	public static function fromPrefix(string $string): self {
		$array = explode('/', trim($string));
		$address = IPv4::factory($array[0]);
		return new self($address, (int) $array[1]);
	}

	/**
	 * Returns the IPv4 address
	 * @return IPv4 IPv4 address
	 */
	public function getAddress(): IPv4 {
		return $this->address;
	}

	/**
	 * Returns the IPv4 address prefix
	 * @return int IPv4 address prefix
	 */
	public function getPrefix(): int {
		return $this->prefix;
	}

	/**
	 * Returns the IPv4 address mask
	 * @return IPv4 IPv4 address mask
	 */
	public function getMask(): IPv4 {
		$mask = 0;
		for ($i = 0; $i < $this->prefix; ++$i) {
			$mask |= 1;
			if ($i < ($this->prefix - 1)) {
				$mask <<= 1;
			}
		}
		$mask <<= (32 - $this->prefix);
		return IPv4::factory(long2ip($mask));
	}

	/**
	 * Converts the IPv4 address entity to an array
	 * @return array{address: string, prefix: int, mask: string} IPv4 address entity in the array
	 */
	public function toArray(): array {
		return [
			'address' => $this->address->getDotAddress(),
			'prefix' => $this->prefix,
			'mask' => $this->getMask()->getDotAddress(),
		];
	}

	/**
	 * Converts the IPv4 address entity to a string
	 * @return string IPv4 address with prefix
	 */
	public function toString(): string {
		return $this->address->getDotAddress() . '/' . $this->prefix;
	}

}
