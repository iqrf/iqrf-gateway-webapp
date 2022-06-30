<?php

/**
 * TEST: App\Models\Database\Entities\Mapping
 * @covers App\Models\Database\Entities\Mapping
 * @phpVersion >= 7.4
 * @testCase
 */
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

namespace Tests\Unit\Models\Database\Entities;

use App\Models\Database\Entities\Mapping;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for mapping database entity
 */
class MappingTest extends TestCase {

	/**
	 * @var Mapping Mapping entity
	 */
	private Mapping $mapping;

	/**
	 * @var Mapping Mapping entity for Gateway
	 */
	private Mapping $mappingGw;

	/**
	 * @var string Mapping type
	 */
	private const TYPE = 'uart';

	/**
	 * @var string Mapping name
	 */
	private const NAME = 'Test mapping 1';

	/**
	 * @var string Mapping device name
	 */
	private const INTERFACE = '/dev/ttyS0';

	/**
	 * @var int Mapping bus enable pin number
	 */
	private const BUS_PIN = 19;

	/**
	 * @var int Mapping programming mode switch pin number
	 */
	private const PGM_PIN = -1;

	/**
	 * @var int Mapping power enable pin number
	 */
	private const POWER_PIN = 3;

	/**
	 * @var int Mapping UART baud rate
	 */
	private const UART_BAUD_RATE = 57600;

	/**
	 * @var int Mapping I2C interface enable pin number
	 */
	private const I2C_PIN = 7;

	/**
	 * @var int Mapping SPI interface enable pin number
	 */
	private const SPI_PIN = 10;

	/**
	 * @var int Mapping UART interface enable pin number
	 */
	private const UART_PIN = 6;

	/**
	 * Sets up testing environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->mapping = new Mapping(self::TYPE, self::NAME, self::INTERFACE, self::BUS_PIN, self::PGM_PIN, self::POWER_PIN, self::UART_BAUD_RATE);
		$this->mappingGw = new Mapping(self::TYPE, self::NAME, self::INTERFACE, self::BUS_PIN, self::PGM_PIN, self::POWER_PIN, self::UART_BAUD_RATE, self::I2C_PIN, self::SPI_PIN, self::UART_PIN);
	}

	/**
	 * Tests the function to return mapping name
	 */
	public function testGetName(): void {
		Assert::same(self::NAME, $this->mapping->getName());
	}

	/**
	 * Tests the function to set mapping name
	 */
	public function testSetName(): void {
		$expected = 'Test mapping 2';
		$this->mapping->setName($expected);
		Assert::same($expected, $this->mapping->getName());
	}

	/**
	 * Tests the function to return mapping device name
	 */
	public function testGetInterface(): void {
		Assert::same(self::INTERFACE, $this->mapping->getInterface());
	}

	/**
	 * Tests the function to set mapping device name
	 */
	public function testSetInterface(): void {
		$expected = '/dev/ttyS1';
		$this->mapping->setInterface($expected);
		Assert::same($expected, $this->mapping->getInterface());
	}

	/**
	 * Tests the function to return mapping bus enable pin number
	 */
	public function testGetBusPin(): void {
		Assert::same(self::BUS_PIN, $this->mapping->getBusPin());
	}

	/**
	 * Tests the function to set mapping bus enable pin number
	 */
	public function testSetBusPin(): void {
		$expected = -1;
		$this->mapping->setBusPin($expected);
		Assert::same($expected, $this->mapping->getBusPin());
	}

	/**
	 * Tests the function to return mapping programming mode switch pin number
	 */
	public function testGetPgmPin(): void {
		Assert::same(self::PGM_PIN, $this->mapping->getPgmPin());
	}

	/**
	 * Tests the function to set mapping programming mode switch pin number
	 */
	public function testSetPgmPin(): void {
		$expected = 1;
		$this->mapping->setPgmPin($expected);
		Assert::same($expected, $this->mapping->getPgmPin());
	}

	/**
	 * Tests the function to return mapping power enable pin number
	 */
	public function testGetPowerPin(): void {
		Assert::same(self::POWER_PIN, $this->mapping->getPowerPin());
	}

	/**
	 * Tests the function to set mapping power enable pin number
	 */
	public function testSetPowerPin(): void {
		$expected = 4;
		$this->mapping->setPowerPin($expected);
		Assert::same($expected, $this->mapping->getPowerPin());
	}

	/**
	 * Tests the function to return mapping UART baud rate
	 */
	public function testGetBaudRate(): void {
		Assert::same(self::UART_BAUD_RATE, $this->mapping->getBaudRate());
	}

	/**
	 * Tests the function to clear mapping UART baud rate
	 */
	public function testSetBaudRateNull(): void {
		$this->mapping->setBaudRate();
		Assert::null($this->mapping->getBaudRate());
	}

	/**
	 * Tests the function to set mapping UART baud rate with supported value
	 */
	public function testSetBaudRateValid(): void {
		$expected = 19200;
		$this->mapping->setBaudRate($expected);
		Assert::same($expected, $this->mapping->getBaudRate());
	}

	/**
	 * Tests the function to set mapping UART baud rate with invalid value
	 */
	public function testSetBaudRateInvalid(): void {
		$this->mapping->setBaudRate(20);
		Assert::same(self::UART_BAUD_RATE, $this->mapping->getBaudRate());
	}

	/**
	 * Tests the function to return mapping I2C interface enable pin number
	 */
	public function testGetI2cPin(): void {
		Assert::same(self::I2C_PIN, $this->mappingGw->getI2cPin());
	}

	/**
	 * Tests the function to return mapping I2C interface enable pin number for non Gateway mapping
	 */
	public function testGetI2cPinNull(): void {
		Assert::null($this->mapping->getI2cPin());
	}

	/**
	 * Tests the function to set mapping I2C interface enable pin number
	 */
	public function testSetI2cPin(): void {
		$expected = 1;
		$this->mappingGw->setI2cPin($expected);
		Assert::same($expected, $this->mappingGw->getI2cPin());
	}

	/**
	 * Tests the function to return mapping SPI interface enable pin number
	 */
	public function testGetSpiPin(): void {
		Assert::same(self::SPI_PIN, $this->mappingGw->getSpiPin());
	}

	/**
	 * Tests the function to return mapping SPI interface enable pin number for non Gateway mapping
	 */
	public function testGetSpiPinNull(): void {
		Assert::null($this->mapping->getSpiPin());
	}

	/**
	 * Tests the function to set mapping SPI interface enable pin number
	 */
	public function testSetSpiPin(): void {
		$expected = -1;
		$this->mappingGw->setSpiPin($expected);
		Assert::same($expected, $this->mappingGw->getSpiPin());
	}

	/**
	 * Tests the function to return mapping UART interface enable pin number
	 */
	public function testGetUartPin(): void {
		Assert::same(self::UART_PIN, $this->mappingGw->getUartPin());
	}

	/**
	 * Tests the function to return mapping UART interface enable pin number for non Gateway mapping
	 */
	public function testGetUartPinNull(): void {
		Assert::null($this->mapping->getUartPin());
	}

	/**
	 * Tests the function to set mapping UART interface enable pin number
	 */
	public function testSetUartPin(): void {
		$expected = 5;
		$this->mappingGw->setUartPin($expected);
		Assert::same($expected, $this->mappingGw->getUartPin());
	}

	/**
	 * Tests the function to return mapping type
	 */
	public function testGetType(): void {
		Assert::same(self::TYPE, $this->mapping->getType());
	}

	/**
	 * Tests the function to mapping type with unsupported type
	 */
	public function testSetTypeUnsupported(): void {
		$this->mapping->setType('test');
		Assert::same(self::TYPE, $this->mapping->getType());
	}

	/**
	 * Tests the function to set mapping type, changing type from UART to SPI clears baud rate
	 */
	public function testSetTypeUartToSpi(): void {
		$expected = 'spi';
		$this->mapping->setType($expected);
		Assert::same($expected, $this->mapping->getType());
		Assert::null($this->mapping->getBaudRate());
	}

	/**
	 * Tests the function to return JSON serialized mapping data of UART type
	 */
	public function testJsonSerializeUart(): void {
		$expected = [
			'id' => null,
			'type' => self::TYPE,
			'name' => self::NAME,
			'IqrfInterface' => self::INTERFACE,
			'busEnableGpioPin' => self::BUS_PIN,
			'pgmSwitchGpioPin' => self::PGM_PIN,
			'powerEnableGpioPin' => self::POWER_PIN,
			'baudRate' => self::UART_BAUD_RATE,
		];
		Assert::same($expected, $this->mapping->jsonSerialize());
	}

	/**
	 * Tests the function to return JSON serialized mapping data of SPI type
	 */
	public function testJsonSerializeSpi(): void {
		$expected = [
			'id' => null,
			'type' => 'spi',
			'name' => self::NAME,
			'IqrfInterface' => self::INTERFACE,
			'busEnableGpioPin' => self::BUS_PIN,
			'pgmSwitchGpioPin' => self::PGM_PIN,
			'powerEnableGpioPin' => self::POWER_PIN,
		];
		$this->mapping->setType('spi');
		Assert::same($expected, $this->mapping->jsonSerialize());
	}

	/**
	 * Tests the function to return JSON serialized mapping data with Gateway only pins
	 */
	public function testJsonSerializeGw(): void {
		$expected = [
			'id' => null,
			'type' => self::TYPE,
			'name' => self::NAME,
			'IqrfInterface' => self::INTERFACE,
			'busEnableGpioPin' => self::BUS_PIN,
			'pgmSwitchGpioPin' => self::PGM_PIN,
			'powerEnableGpioPin' => self::POWER_PIN,
			'baudRate' => self::UART_BAUD_RATE,
			'i2cEnableGpioPin' => self::I2C_PIN,
			'spiEnableGpioPin' => self::SPI_PIN,
			'uartEnableGpioPin' => self::UART_PIN,
		];
		Assert::same($expected, $this->mappingGw->jsonSerialize());
	}

}

$test = new MappingTest();
$test->run();
