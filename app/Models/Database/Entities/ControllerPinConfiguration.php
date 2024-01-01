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

namespace App\Models\Database\Entities;

use App\ConfigModule\Enums\DeviceTypes;
use App\Models\Database\Attributes\TId;
use App\Models\Database\Repositories\ControllerPinConfigurationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * Controller pins entity
 */
#[ORM\Entity(repositoryClass: ControllerPinConfigurationRepository::class)]
#[ORM\Table(name: 'controller_pin_configs')]
class ControllerPinConfiguration implements JsonSerializable {

	use TId;

	/**
	 * Constructor
	 * @param string $name Controller pins name
	 * @param DeviceTypes $deviceType Device type
	 * @param int $greenLed Green LED pin
	 * @param int $redLed Red LED pin
	 * @param int $button Button pin
	 * @param int|null $sck SCK pin
	 * @param int|null $sda SDA pin
	 */
	public function __construct(
		#[ORM\Column(type: Types::STRING, length: 255)]
		private string $name,
		#[ORM\Column(type: Types::STRING, length: 255, enumType: DeviceTypes::class)]
		private DeviceTypes $deviceType,
		#[ORM\Column(type: Types::INTEGER)]
		private int $greenLed,
		#[ORM\Column(type: Types::INTEGER)]
		private int $redLed,
		#[ORM\Column(type: Types::INTEGER)]
		private int $button,
		#[ORM\Column(type: Types::INTEGER, nullable: true)]
		private ?int $sck = null,
		#[ORM\Column(type: Types::INTEGER, nullable: true)]
		private ?int $sda = null,
	) {
	}

	/**
	 * Returns controller pins name
	 * @return string Controller pins name
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Sets controller pins name
	 * @param string $name Controller pins name
	 */
	public function setName(string $name): void {
		$this->name = $name;
	}

	/**
	 * Returns device type
	 * @return DeviceTypes Device type
	 */
	public function getDeviceType(): DeviceTypes {
		return $this->deviceType;
	}

	/**
	 * Sets device type
	 * @param DeviceTypes $deviceType Device type
	 */
	public function setDeviceType(DeviceTypes $deviceType): void {
		$this->deviceType = $deviceType;
	}

	/**
	 * Returns green LED pin
	 * @return int Green LED pin
	 */
	public function getGreenLedPin(): int {
		return $this->greenLed;
	}

	/**
	 * Sets green LED pin
	 * @param int $greenLed Green LED pin
	 */
	public function setGreenLedPin(int $greenLed): void {
		$this->greenLed = $greenLed;
	}

	/**
	 * Return red LED pin
	 * @return int Red LED pin
	 */
	public function getRedLedPin(): int {
		return $this->redLed;
	}

	/**
	 * Sets red LED pin
	 * @param int $redLed Red LED pin
	 */
	public function setRedLedPin(int $redLed): void {
		$this->redLed = $redLed;
	}

	/**
	 * Return button pin
	 * @return int Button pin
	 */
	public function getButtonPin(): int {
		return $this->button;
	}

	/**
	 * Sets button pin
	 * @param int $button Button pin
	 */
	public function setButtonPin(int $button): void {
		$this->button = $button;
	}

	/**
	 * Returns SCK pin
	 * @return int|null SCK pin
	 */
	public function getSckPin(): ?int {
		return $this->sck;
	}

	/**
	 * Sets SCK pin
	 * @param int|null $sck SCK pin
	 */
	public function setSckPin(?int $sck = null): void {
		$this->sck = $sck;
	}

	/**
	 * Returns SDA pin
	 * @return int|null SDA pin
	 */
	public function getSdaPin(): ?int {
		return $this->sda;
	}

	/**
	 * Sets SDA pin
	 * @param int|null $sda SDA pin
	 */
	public function setSdaPin(?int $sda = null): void {
		$this->sda = $sda;
	}

	/**
	 * Returns JSON serialized controller pins
	 * @return array{id: int|null, name: string, deviceType: string, greenLed: int, redLed: int, button: int, sck?: int, sda?: int} JSON serialized controller pins
	 */
	public function jsonSerialize(): array {
		$array = [
			'id' => $this->getId(),
			'name' => $this->name,
			'deviceType' => $this->deviceType->value,
			'greenLed' => $this->greenLed,
			'redLed' => $this->redLed,
			'button' => $this->button,
		];
		if ($this->sck !== null) {
			$array['sck'] = $this->sck;
		}
		if ($this->sda !== null) {
			$array['sda'] = $this->sda;
		}
		return $array;
	}

}
