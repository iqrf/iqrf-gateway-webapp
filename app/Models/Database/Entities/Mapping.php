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

namespace App\Models\Database\Entities;

use App\Models\Database\Attributes\TId;
use App\Models\Database\Enums\MappingBaudRate;
use App\Models\Database\Enums\MappingDeviceType;
use App\Models\Database\Enums\MappingType;
use App\Models\Database\Repositories\MappingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * Mapping entity
 */
#[ORM\Entity(repositoryClass: MappingRepository::class)]
#[ORM\Table(name: 'mappings')]
#[ORM\HasLifecycleCallbacks]
class Mapping implements JsonSerializable {

	use TId;

	/**
	 * Constructor
	 * @param MappingType $type Mapping type
	 * @param string $name Mapping name
	 * @param MappingDeviceType $deviceType Device type
	 * @param string $iqrfInterface Mapping device name
	 * @param int $busEnableGpioPin Mapping bus enable pin
	 * @param int $pgmSwitchGpioPin Mapping programming mode switch pin
	 * @param int $powerEnableGpioPin Mapping power enable pin
	 * @param MappingBaudRate|null $baudRate Mapping UART baud rate
	 * @param int|null $i2cEnableGpioPin Mapping I2C interface enable pin
	 * @param int|null $spiEnableGpioPin Mapping SPI interface enable pin
	 * @param int|null $uartEnableGpioPin Mapping UART interface enable pin
	 */
	public function __construct(
		#[ORM\Column(type: Types::STRING, length: 255, enumType: MappingType::class)]
		private MappingType $type,
		#[ORM\Column(type: Types::STRING, length: 255)]
		private string $name,
		#[ORM\Column(type: Types::STRING, length: 255, enumType: MappingDeviceType::class)]
		private MappingDeviceType $deviceType,
		#[ORM\Column(type: Types::STRING, length: 255)]
		private string $iqrfInterface,
		#[ORM\Column(type: Types::INTEGER)]
		private int $busEnableGpioPin,
		#[ORM\Column(type: Types::INTEGER)]
		private int $pgmSwitchGpioPin,
		#[ORM\Column(type: Types::INTEGER)]
		private int $powerEnableGpioPin,
		#[ORM\Column(type: Types::INTEGER, nullable: true, enumType: MappingBaudRate::class)]
		private ?MappingBaudRate $baudRate = null,
		#[ORM\Column(type: Types::INTEGER, nullable: true)]
		private ?int $i2cEnableGpioPin = null,
		#[ORM\Column(type: Types::INTEGER, nullable: true)]
		private ?int $spiEnableGpioPin = null,
		#[ORM\Column(type: Types::INTEGER, nullable: true)]
		private ?int $uartEnableGpioPin = null,
	) {
	}

	/**
	 * Returns mapping type
	 * @return MappingType Mapping type
	 */
	public function getType(): MappingType {
		return $this->type;
	}

	/**
	 * Sets new mapping type
	 * @param MappingType $type Mapping type
	 */
	public function setType(MappingType $type): void {
		if ($this->type === MappingType::UART && $type === MappingType::SPI) {
			$this->setBaudRate();
		}
		$this->type = $type;
	}

	/**
	 * Returns mapping name
	 * @return string Mapping name
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Sets new mapping name
	 * @param string $name Mapping name
	 */
	public function setName(string $name): void {
		$this->name = $name;
	}

	/**
	 * Returns device type
	 * @return MappingDeviceType Device type
	 */
	public function getDeviceType(): MappingDeviceType {
		return $this->deviceType;
	}

	/**
	 * Set device type
	 * @param MappingDeviceType $deviceType Device type
	 */
	public function setDeviceType(MappingDeviceType $deviceType): void {
		$this->deviceType = $deviceType;
	}

	/**
	 * Returns mapping device name
	 * @return string Mapping device name
	 */
	public function getInterface(): string {
		return $this->iqrfInterface;
	}

	/**
	 * Sets new mapping device name
	 * @param string $interface Mapping device name
	 */
	public function setInterface(string $interface): void {
		$this->iqrfInterface = $interface;
	}

	/**
	 * Returns mapping bus enable pin number
	 * @return int Mapping bus enable pin number
	 */
	public function getBusPin(): int {
		return $this->busEnableGpioPin;
	}

	/**
	 * Sets new bus enable pin number number
	 * @param int $busPin Mapping bus enable pin number
	 */
	public function setBusPin(int $busPin): void {
		$this->busEnableGpioPin = $busPin;
	}

	/**
	 * Returns mapping programming mode switch pin number
	 * @return int Mapping programming mode switch pin number
	 */
	public function getPgmPin(): int {
		return $this->pgmSwitchGpioPin;
	}

	/**
	 * Sets new mapping programming mode switch pin number
	 * @param int $pgmPin Mapping programming mode switch pin number
	 */
	public function setPgmPin(int $pgmPin): void {
		$this->pgmSwitchGpioPin = $pgmPin;
	}

	/**
	 * Returns mapping power enable pin number
	 * @return int Mapping power enable pin number
	 */
	public function getPowerPin(): int {
		return $this->powerEnableGpioPin;
	}

	/**
	 * Sets new power enable pin number
	 * @param int $powerPin Mapping power enable pin number
	 */
	public function setPowerPin(int $powerPin): void {
		$this->powerEnableGpioPin = $powerPin;
	}

	/**
	 * Returns mapping UART baud rate
	 * @return MappingBaudRate|null Mapping UART baud rate
	 */
	public function getBaudRate(): ?MappingBaudRate {
		return $this->baudRate;
	}

	/**
	 * Sets new UART baud rate
	 * @param MappingBaudRate|null $baudRate Mapping UART baud rate
	 */
	public function setBaudRate(?MappingBaudRate $baudRate = null): void {
		$this->baudRate = $baudRate;
	}

	/**
	 * Returns mapping I2C interface enable pin number
	 */
	public function getI2cPin(): ?int {
		return $this->i2cEnableGpioPin;
	}

	/**
	 * Sets new I2C interface enable pin number
	 * @param int|null $i2cPin Mapping I2C interface enable pin number
	 */
	public function setI2cPin(?int $i2cPin = null): void {
		$this->i2cEnableGpioPin = $i2cPin;
	}

	/**
	 * Returns mapping SPI interface enable pin number
	 */
	public function getSpiPin(): ?int {
		return $this->spiEnableGpioPin;
	}

	/**
	 * Sets new SPI interface enable pin number
	 * @param int|null $spiPin Mapping SPI interface enable pin number
	 */
	public function setSpiPin(?int $spiPin = null): void {
		$this->spiEnableGpioPin = $spiPin;
	}

	/**
	 * Returns mapping UART interface enable pin number
	 */
	public function getUartPin(): ?int {
		return $this->uartEnableGpioPin;
	}

	/**
	 * Sets new UART interface enable pin number
	 * @param int|null $uartPin Mapping UART interface enable pin number
	 */
	public function setUartPin(?int $uartPin = null): void {
		$this->uartEnableGpioPin = $uartPin;
	}

	/**
	 * Returns JSON serialized mapping data
	 * @return array<string, string|int> JSON serialized mapping data
	 */
	public function jsonSerialize(): array {
		$array = [
			'id' => $this->getId(),
			'type' => $this->getType()->value,
			'name' => $this->getName(),
			'deviceType' => $this->getDeviceType()->value,
			'IqrfInterface' => $this->getInterface(),
			'busEnableGpioPin' => $this->getBusPin(),
			'pgmSwitchGpioPin' => $this->getPgmPin(),
			'powerEnableGpioPin' => $this->getPowerPin(),
		];
		if ($this->getType() === MappingType::UART) {
			$array['baudRate'] = $this->getBaudRate()->value;
		}
		if ($this->getI2cPin() !== null) {
			$array['i2cEnableGpioPin'] = $this->getI2cPin();
		}
		if ($this->getSpiPin() !== null) {
			$array['spiEnableGpioPin'] = $this->getSpiPin();
		}
		if ($this->getUartPin() !== null) {
			$array['uartEnableGpioPin'] = $this->getUartPin();
		}
		return $array;
	}

}
