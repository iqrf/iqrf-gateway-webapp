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

use App\NetworkModule\Enums\ModemFailedReason;
use App\NetworkModule\Enums\ModemState;
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
	 * @var string $imei Modem IMEI
	 */
	private string $imei;

	/**
	 * @var string $manufacturer Modem manufacturer
	 */
	private string $manufacturer;

	/**
	 * @var string $model Modem model
	 */
	private string $model;

	/**
	 * @var int $signal Signal strength
	 */
	private int $signal;

	/**
	 * @var ModemState $state Modem state
	 */
	private ModemState $state;

	/**
	 * @var ModemFailedReason|null $failedReason Modem failed reason
	 */
	private ?ModemFailedReason $failedReason;

	/**
	 * @var float|null $rssi RSSI
	 */
	private ?float $rssi = null;

	/**
	 * Constructor
	 * @param string $interface Modem network interface
	 * @param string $imei Modem IMEI
	 * @param string $manufacturer Modem manufacturer
	 * @param string $model Modem model
	 * @param ModemState $state Modem state
	 * @param ModemFailedReason|null $failedReason Modem failed reason
	 * @param int $signal Signal strength
	 */
	public function __construct(string $interface, string $imei, string $manufacturer, string $model, ModemState $state, ?ModemFailedReason $failedReason, int $signal) {
		$this->interface = $interface;
		$this->imei = $imei;
		$this->manufacturer = $manufacturer;
		$this->model = $model;
		$this->state = $state;
		$this->failedReason = $failedReason;
		$this->signal = $signal;
	}

	/**
	 * Sets modem RSSI
	 * @param float|null $rssi RSSI
	 */
	public function setRssi(?float $rssi): void {
		$this->rssi = $rssi;
	}

	/**
	 * Creates a new Modem entity from mmcli json
	 * @param stdClass $modem mmcli modem json object
	 * @param stdClass|null $signal mmcli modem rssi json object
	 * @return Modem Modem entity
	 */
	public static function fromMmcliJson(stdClass $modem, ?stdClass $signal): self {
		$interface = $modem->modem->generic->{'primary-port'};
		$imei = $modem->modem->generic->{'equipment-identifier'};
		$manufacturer = $modem->modem->generic->{'manufacturer'};
		$model = $modem->modem->generic->{'model'};
		$state = ModemState::fromScalar($modem->modem->generic->{'state'});
		$failedReason = $modem->modem->generic->{'state-failed-reason'};
		$failedReason = $failedReason === '--' ? null : ModemFailedReason::fromScalar($failedReason);
		$signalQuality = (int) $modem->modem->generic->{'signal-quality'}->value;
		$rssi = $signal?->modem->signal->gsm->rssi ?? null;
		$entity = new self($interface, $imei, $manufacturer, $model, $state, $failedReason, $signalQuality);
		if ($rssi !== null) {
			$entity->setRssi((float) $rssi);
		}
		return $entity;
	}

	/**
	 * Serializes the Modem entity to JSON
	 * @return array{interface: string, imei: string, manufacturer: string|null, model: string|null, state: string, failedReason: string|null, signal: int, rssi: float|null} JSON serialized entity
	 */
	public function jsonSerialize(): array {
		return [
			'interface' => $this->interface,
			'imei' => $this->imei,
			'manufacturer' => $this->manufacturer,
			'model' => $this->model,
			'state' => $this->state->toScalar(),
			'failedReason' => $this->failedReason?->toScalar(),
			'signal' => $this->signal,
			'rssi' => $this->rssi,
		];
	}

}
