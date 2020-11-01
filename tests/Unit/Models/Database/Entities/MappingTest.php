<?php

/**
 * TEST: App\Models\Database\Entities\Mapping
 * @covers App\Models\Database\Entities\Mapping
 * @phpVersion >= 7.1
 * @testCase
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
	private $mapping;

	/**
	 * Mapping type
	 */
	private const TYPE = 'uart';

	/**
	 * Mapping name
	 */
	private const NAME = 'Test mapping 1';

	/**
	 * Mapping device name
	 */
	private const INTERFACE = '/dev/ttyS0';

	/**
	 * Mapping bus enable pin number
	 */
	private const BUS_PIN = 19;

	/**
	 * Mapping programming mode switch pin number
	 */
	private const PGM_PIN = -1;

	/**
	 * Mapping power enable pin number
	 */
	private const POWER_PIN = 3;

	/**
	 * Mapping UART baud rate
	 */
	private const UART_BAUD_RATE = 57600;

	/**
	 * Sets up testing environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->mapping = new Mapping(self::TYPE, self::NAME, self::INTERFACE, self::BUS_PIN, self::PGM_PIN, self::POWER_PIN, self::UART_BAUD_RATE);
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
		$this->mapping->setBaudRate($expected = 19200);
		Assert::same($expected = 19200, $this->mapping->getBaudRate());
	}

	/**
	 * Tests the function to set mapping UART baud rate with invalid value
	 */
	public function testSetBaudRateInvalid(): void {
		$this->mapping->setBaudRate(20);
		Assert::same(self::UART_BAUD_RATE, $this->mapping->getBaudRate());
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

}

$test = new MappingTest();
$test->run();
