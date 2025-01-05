<?php

/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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

use Darsyn\IP\Version\Multi as IP;

class MultiAddress {

	/**
	 * @var IP IP address
	 */
	private IP $address;

	/**
	 * @var int IP address prefix
	 */
	private int $prefix;

	/**
	 * Constructor
	 * @param IP $address IP address
	 * @param int $prefix IP address prefix
	 */
	public function __construct(IP $address, int $prefix) {
		$this->address = $address;
		$this->prefix = $prefix;
	}

	/**
	 * Creates a new IP address entity from the address with prefix
	 * @param string $string IP address with prefix
	 * @return MultiAddress IP address entity
	 */
	public static function fromPrefix(string $string): self {
		$array = explode('/', trim($string));
		$address = IP::factory($array[0]);
		return new self($address, (int) $array[1]);
	}

	/**
	 * Returns the IP address
	 * @return IP IP address
	 */
	public function getAddress(): IP {
		return $this->address;
	}

	/**
	 * Returns the IP address prefix
	 * @return int IP address prefix
	 */
	public function getPrefix(): int {
		return $this->prefix;
	}

	/**
	 * Returns the IP version
	 * @return int IP version
	 */
	public function getVersion(): int {
		return $this->address->getVersion();
	}

	/**
	 * Converts the Multi address address entity to a string
	 * @return string IP address address with prefix
	 */
	public function toString(): string {
		if ($this->getVersion() === 4) {
			return $this->address->getDotAddress() . '/' . $this->prefix;
		}
		return $this->address->getCompactedAddress() . '/' . $this->prefix;
	}

}
