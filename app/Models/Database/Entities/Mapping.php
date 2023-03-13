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
use App\Models\Database\Repositories\MappingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use function in_array;

/**
 * Mapping entity
 */
#[ORM\Entity(repositoryClass: MappingRepository::class)]
#[ORM\Table(name: 'mappings')]
#[ORM\HasLifecycleCallbacks]
class Mapping implements JsonSerializable {

	use TId;

	/**
	 * @var string Mapping type: SPI
	 */
	public const TYPE_SPI = 'spi';

	/**
	 * @var string Mapping type: UART
	 */
	public const TYPE_UART = 'uart';

	/**
	 * @var array<string> Supported mapping types
	 */
	public const TYPES = [self::TYPE_SPI, self::TYPE_UART];

	/**
	 * @var int Default mapping UART baud rate
	 */
	public const BAUD_RATE_DEFAULT = 57600;

	/**
	 * @var array<int> Supported mapping UART baud rates
	 */
	public const BAUD_RATES = [1200, 2400, 4800, 9600, 19200, 38400, 57600, 115200, 230400];

	/**
	 * @var string Device type: Adapter
	 */
	public const DEVICE_ADAPTER = 'adapter';

	/**
	 * @var string Device type: Board
	 */
	public const DEVICE_BOARD = 'board';

	/**
	 * @var array<string> Supported device types
	 */
	public const DEVICE_TYPES = [self::DEVICE_ADAPTER, self::DEVICE_BOARD];

	/**
	 * @var string Mapping type
	 */
	#[ORM\Column(type: Types::STRING, length: 255)]
	private string $type;

	/**
	 * @var string Mapping name
	 */
	#[ORM\Column(type: Types::STRING, length: 255)]
	private string $name;

	/**
	 * @var string Device type
	 */
	#[ORM\Column(type: Types::STRING, length: 255)]
	private string $deviceType;

	/**
	 * @var string Device name
	 */
	#[ORM\Column(type: Types::STRING, length: 255)]
	private string $iqrfInterface;

	/**
	 * @var int Bus enable pin
	 */
	#[ORM\Column(type: Types::INTEGER)]
	private int $busEnableGpioPin;

	/**
	 * @var int Programming mode switch pin
	 */
	#[ORM\Column(type: Types::INTEGER)]
	private int $pgmSwitchGpioPin;

	/**
	 * @var int Power enable pin
	 */
	#[ORM\Column(type: Types::INTEGER)]
	private int $powerEnableGpioPin;

	/**
	 * @var int|null UART baud rate
	 */
	#[ORM\Column(type: Types::INTEGER, nullable: true)]
	private ?int $baudRate;

	/**
	 * @var int|null I2C interface enable pin
	 */
	#[ORM\Column(type: Types::INTEGER, nullable: true)]
	private ?int $i2cEnableGpioPin;

	/**
	 * @var int|null SPI interface enable pin
	 */
	#[ORM\Column(type: Types::INTEGER, nullable: true)]
	private ?int $spiEnableGpioPin;

	/**
	 * @var int|null UART interface enable pin
	 */
	#[ORM\Column(type: Types::INTEGER, nullable: true)]
	private ?int $uartEnableGpioPin;

	/**
	 * Constructor
	 * @param string $type Mapping type
	 * @param string $name Mapping name
	 * @param string $deviceType Device type
	 * @param string $iqrfInterface Mapping device name
	 * @param int $busEnableGpioPin Mapping bus enable pin
	 * @param int $pgmSwitchGpioPin Mapping programming mode switch pin
	 * @param int $powerEnableGpioPin Mapping power enable pin
	 * @param int|null $baudRate Mapping UART baud rate
	 * @param int|null $i2cEnableGpioPin Mapping I2C interface enable pin
	 * @param int|null $spiEnableGpioPin Mapping SPI interface enable pin
	 * @param int|null $uartEnableGpioPin Mapping UART interface enable pin
	 */
	public function __construct(string $type, string $name, string $deviceType, string $iqrfInterface, int $busEnableGpioPin, int $pgmSwitchGpioPin, int $powerEnableGpioPin, ?int $baudRate = null, ?int $i2cEnableGpioPin = null, ?int $spiEnableGpioPin = null, ?int $uartEnableGpioPin = null) {
		$this->type = $type;
		$this->name = $name;
		$this->deviceType = $deviceType;
		$this->iqrfInterface = $iqrfInterface;
		$this->busEnableGpioPin = $busEnableGpioPin;
		$this->pgmSwitchGpioPin = $pgmSwitchGpioPin;
		$this->powerEnableGpioPin = $powerEnableGpioPin;
		$this->baudRate = $baudRate;
		$this->i2cEnableGpioPin = $i2cEnableGpioPin;
		$this->spiEnableGpioPin = $spiEnableGpioPin;
		$this->uartEnableGpioPin = $uartEnableGpioPin;
	}

	/**
	 * Returns mapping type
	 * @return string Mapping type
	 */
	public function getType(): string {
		return $this->type;
	}

	/**
	 * Sets new mapping type
	 * @param string $type Mapping type
	 */
	public function setType(string $type): void {
		if (!in_array($type, self::TYPES, true)) {
			return;
		}
		if ($this->type === self::TYPE_UART && $type === self::TYPE_SPI) {
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
	 * @return string Device type
	 */
	public function getDeviceType(): string {
		return $this->deviceType;
	}

	/**
	 * Set device type
	 * @param string $deviceType Device type
	 */
	public function setDeviceType(string $deviceType): void {
		if (!in_array($deviceType, self::DEVICE_TYPES, true)) {
			return;
		}
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
	 * @return int|null Mapping UART baud rate
	 */
	public function getBaudRate(): ?int {
		return $this->baudRate;
	}

	/**
	 * Sets new UART baud rate
	 * @param int|null $baudRate Mapping UART baud rate
	 */
	public function setBaudRate(?int $baudRate = null): void {
		if ($baudRate !== null && !in_array($baudRate, self::BAUD_RATES, true)) {
			return;
		}
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
			'type' => $this->getType(),
			'name' => $this->getName(),
			'deviceType' => $this->getDeviceType(),
			'IqrfInterface' => $this->getInterface(),
			'busEnableGpioPin' => $this->getBusPin(),
			'pgmSwitchGpioPin' => $this->getPgmPin(),
			'powerEnableGpioPin' => $this->getPowerPin(),
		];
		if ($this->getType() === self::TYPE_UART) {
			$array['baudRate'] = $this->getBaudRate();
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
