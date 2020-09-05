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

namespace App\IqrfNetModule\Entities;

use App\IqrfNetModule\Enums\DeviceTypes;

/**
 * Device status entity
 */
class DeviceStatus {

	/**
	 * @var int Network address
	 */
	private $address;

	/**
	 * @var int Device status type
	 */
	private $type = DeviceTypes::NONE;

	/**
	 * Constructor
	 * @param int $address Network address
	 */
	public function __construct(int $address) {
		$this->address = $address;
	}

	/**
	 * Returns network address
	 * @return int Network address
	 */
	public function getAddress(): int {
		return $this->address;
	}

	/**
	 * Returns HTML icon element
	 * @return string HTML icon element
	 */
	public function getIcon(): string {
		switch ($this->type) {
			case DeviceTypes::COORDINATOR:
				return '<span class=\'cil-home text-info\'></span>';
			case DeviceTypes::BONDED:
				return '<span class=\'cil-check-alt text-primary\'></span>';
			case DeviceTypes::DISCOVERED:
				return '<span class=\'cil-signal-cellular-4 text-primary\'></span>';
			case DeviceTypes::BONDED_ONLINE:
				return '<span class=\'cil-check-alt text-success\'></span>';
			case DeviceTypes::DISCOVERED_ONLINE:
				return '<span class=\'cil-signal-cellular-4 text-success\'></span>';
			default:
				return '<span class=\'cil-x text-danger\'></span>';
		}
	}

	/**
	 * Returns device status type
	 * @return int Device status type
	 */
	public function getType(): int {
		return $this->type;
	}

	/**
	 * Sets device status type
	 * @param int $type Device status type
	 */
	public function setType(int $type): void {
		if ($type === DeviceTypes::ONLINE) {
			$this->type += 3;
		} else {
			$this->type = $type;
		}
	}

}
