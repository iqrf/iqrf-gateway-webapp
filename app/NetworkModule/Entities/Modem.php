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

use App\NetworkModule\Enums\ModemStates;
use stdClass;

/**
 * Modem entity
 */
class Modem {

	/**
	 * @var string $manufacturer Modem manufacturer
	 */
	private $manufacturer;

	/**
	 * @var string $model Model and revision
	 */
	private $model;

	/**
	 * @var int $imei Equipment ID
	 */
	private $imei;

	/**
	 * @var string $interface Modem network interface
	 */
	private $interface;

	/**
	 * @var ModemStates $state Modem state
	 */
	private $state;

	/**
	 * @var string|null $error Error string
	 */
	private $error;

	/**
	 * @var string|null $operator Operator code
	 */
	private $operator;

	/**
	 * @var int|null $signal Signal strength
	 */
	private $signal;

	/**
	 * @var string|null $technology Access technology
	 */
	private $technology;

	/**
	 * @var float|null $rssi RSSI
	 */
	private $rssi;

	/**
	 * Constructor
	 * @param string $manufacturer Manufacturer
	 * @param string $model Model
	 * @param int $imei Equipment ID
	 * @param string $interface Device interface
	 * @param ModemStates $state Modem state
	 * @param string|null $error Error string in failed state
	 * @param string|null $operator Network operator
	 * @param int|null $signal Signal strength
	 * @param string|null $technology Access technology
	 * @param float|null $rssi RSSI
	 */
	public function __construct(string $manufacturer, string $model, int $imei, string $interface, ModemStates $state, ?string $error = null, ?string $operator = null, ?int $signal = null, ?string $technology = null, ?float $rssi = null) {
		$this->manufacturer = $manufacturer;
		$this->model = $model;
		$this->imei = $imei;
		$this->interface = $interface;
		$this->state = $state;
		$this->error = $error;
		$this->operator = $operator;
		$this->signal = $signal;
		$this->technology = $technology;
		$this->rssi = $rssi;
	}

	/**
	 * Creates a new Modem entity from mmcli json
	 * @param stdClass $modem mmcli modem json object
	 * @param stdClass $signal mmcli modem rssi json object
	 * @return Modem Modem entity
	 */
	public static function fromMmcliJson(stdClass $modem, stdClass $signal): self {
		$manufacturer = $modem->modem->generic->manufacturer;
		$model = $modem->modem->generic->model;
		$imei = intval($modem->modem->generic->{'equipment-identifier'});
		$interface = $modem->modem->generic->{'primary-port'};
		$state = ModemStates::fromScalar($modem->modem->generic->state);
		if ($state === ModemStates::FAILED()) {
			$error = $modem->modem->generic->{'state-failed-reason'};
		} else {
			$operator = $modem->modem->{'3gpp'}->{'operator-name'};
			$signalQuality = intval($modem->modem->generic->{'signal-quality'}->value);
			$technology = $modem->modem->generic->{'access-technologies'}[0];
			$rssi = floatval($signal->modem->signal->gsm->rssi);
		}
		return new self($manufacturer, $model, $imei, $interface, $state, $error ?? null, $operator ?? null, $signalQuality ?? null, $technology ?? null, $rssi ?? null);
	}

	/**
	 * Serializes the Modem entity to json
	 * @return array<string, int|float|string> JSON serialized entity
	 */
	public function jsonSerialize(): array {
		$array = [
			'manufacturer' => $this->manufacturer,
			'model' => $this->model,
			'imei' => $this->imei,
			'interface' => $this->interface,
			'state' => $this->state->jsonSerialize(),
		];
		if ($this->state === ModemStates::FAILED()) {
			$array['error'] = $this->error;
		} else {
			$array['operator'] = $this->operator;
			$array['signal'] = $this->signal;
			$array['technology'] = $this->technology;
			$array['rssi'] = $this->rssi;
		}
		return $array;
	}

}
