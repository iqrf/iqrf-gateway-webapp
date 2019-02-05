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

namespace App\IqrfNetModule\Models;

/**
 * IQRF Standard light
 */
class StandardLight {

	/**
	 * @var int Index of the light
	 */
	private $index;

	/**
	 * @var int Power level of the light from range <0;100>
	 */
	private $power;

	/**
	 * Constructor
	 * @param int $index Index of the light
	 * @param int $power Power level of the light from range <0;100>
	 */
	public function __construct(int $index, int $power) {
		$this->index = $index;
		$this->power = $power;
	}

	/**
	 * Get a power level of the light
	 * @return int Power level of the light from range <0;100>
	 */
	public function getPower(): int {
		return $this->power;
	}

	/**
	 * Set a power level of the light
	 * @param int $power Power level of the light from range <0;100>
	 */
	public function setPower(int $power): void {
		$this->power = $power;
	}

	/**
	 * Convert an object to an array
	 * @return int[] Properties in array
	 */
	public function toArray(): array {
		return [
			'index' => $this->index,
			'power' => $this->power,
		];
	}

}
