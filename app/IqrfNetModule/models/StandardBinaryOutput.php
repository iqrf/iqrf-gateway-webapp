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
 * IQRF Standard binary output
 */
class StandardBinaryOutput {

	/**
	 * @var int Index of the binary output
	 */
	private $index;

	/**
	 * @var bool State of the binary output
	 */
	private $state;

	/**
	 * Constructor
	 * @param int $index Index of the binary output
	 * @param bool $state State of the binary output
	 */
	public function __construct(int $index, bool $state) {
		$this->index = $index;
		$this->state = $state;
	}

	/**
	 * Get the binary output's state
	 * @return bool Binary output's state
	 */
	public function getState(): bool {
		return $this->state;
	}

	/**
	 * Set the binary output's state
	 * @param bool $state Binary output's state
	 */
	public function setState(bool $state): void {
		$this->state = $state;
	}

	/**
	 * Convert an object to an array
	 * @return mixed[] Properties in array
	 */
	public function toArray(): array {
		return [
			'index' => $this->index,
			'state' => $this->state,
		];
	}

}
