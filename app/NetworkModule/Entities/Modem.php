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

use stdClass;

/**
 * Modem entity
 */
class Modem {

	/**
	 * @var string $interface Modem network interface
	 */
	private $interface;

	/**
	 * @var int $signal Signal strength
	 */
	private $signal;

	/**
	 * Constructor
	 * @param string $interface Modem network interface
	 * @param int $signal Signal strength
	 */
	public function __construct(string $interface, int $signal) {
		$this->interface = $interface;
		$this->signal = $signal;
	}

	/**
	 * Creates a new Modem entity from mmcli json
	 * @param stdClass $json mmcli json object
	 * @return Modem Modem entity
	 */
	public static function fromMmcliJson(stdClass $json): self {
		$interface = $json->modem->generic->{'primary-port'};
		$signal = $json->modem->generic->{'signal-quality'}->value;
		return new self($interface, $signal);
	}

	/**
	 * Serializes the Modem entity to json
	 * @return array<string, int|string> JSON serialized entity
	 */
	public function jsonSerialize(): array {
		return [
			'interface' => $this->interface,
			'signal' => $this->signal,
		];
	}

}
