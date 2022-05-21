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
	private string $interface;

	/**
	 * @var int $signal Signal strength
	 */
	private int $signal;

	/**
	 * @var float $rssi RSSI
	 */
	private $rssi;

	/**
	 * Constructor
	 * @param string $interface Modem network interface
	 * @param int $signal Signal strength
	 * @param float $rssi RSSI
	 */
	public function __construct(string $interface, int $signal, float $rssi) {
		$this->interface = $interface;
		$this->signal = $signal;
		$this->rssi = $rssi;
	}

	/**
	 * Creates a new Modem entity from mmcli json
	 * @param stdClass $modem mmcli modem json object
	 * @param stdClass $signal mmcli modem rssi json object
	 * @return Modem Modem entity
	 */
	public static function fromMmcliJson(stdClass $modem, stdClass $signal): self {
		$interface = $modem->modem->generic->{'primary-port'};
		$signalQuality = $modem->modem->generic->{'signal-quality'}->value;
		$rssi = $signal->modem->signal->gsm->rssi;
		return new self($interface, intval($signalQuality), floatval($rssi));
	}

	/**
	 * Serializes the Modem entity to json
	 * @return array<string, int|float|string> JSON serialized entity
	 */
	public function jsonSerialize(): array {
		return [
			'interface' => $this->interface,
			'signal' => $this->signal,
			'rssi' => $this->rssi,
		];
	}

}
