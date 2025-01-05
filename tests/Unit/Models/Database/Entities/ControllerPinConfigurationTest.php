<?php

/**
 * TEST: App\Models\Database\Entities\ControllerPinConfiguration
 * @covers App\Models\Database\Entities\ControllerPinConfiguration
 * @phpVersion >= 7.4
 * @testCase
 */
/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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

use App\ConfigModule\Enums\DeviceTypes;
use App\Models\Database\Entities\ControllerPinConfiguration;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for controller pin configuration database entity
 */
class ControllerPinConfigurationTest extends TestCase {

	/**
	 * @var string Profile name
	 */
	private const NAME = 'Test profile';

	/**
	 * @var int Green LED pin number
	 */
	private const GREEN_LED_PIN = 0;

	/**
	 * @var int Red LED pin number
	 */
	private const RED_LED_PIN = 1;

	/**
	 * @var int Button pin number
	 */
	private const BUTTON_PIN = 2;

	/**
	 * @var int I2C clock pin number
	 */
	private const SCK_PIN = 3;

	/**
	 * @var int I2C data pin number
	 */
	private const SDA_PIN = 4;

	/**
	 * @var DeviceTypes Device type
	 */
	private DeviceTypes $deviceType;

	/**
	 * @var ControllerPinConfiguration Configuration profile entity
	 */
	private ControllerPinConfiguration $entity;

	/**
	 * Sets up testing environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->deviceType = DeviceTypes::BOARD();
		$this->entity = new ControllerPinConfiguration(
			self::NAME,
			$this->deviceType,
			self::GREEN_LED_PIN,
			self::RED_LED_PIN,
			self::BUTTON_PIN,
			self::SCK_PIN,
			self::SDA_PIN
		);
	}

	/**
	 * Tests the function to return profile name
	 */
	public function testGetName(): void {
		Assert::same(self::NAME, $this->entity->getName());
	}

	/**
	 * Tests the function to set profile name
	 */
	public function testSetName(): void {
		$expected = 'Test profile 1';
		$this->entity->setName($expected);
		Assert::same($expected, $this->entity->getName());
	}

	/**
	 * Tests the function to return device type
	 */
	public function testGetDeviceType(): void {
		Assert::same($this->deviceType, $this->entity->getDeviceType());
	}

	/**
	 * Tests the function to set device type
	 */
	public function testSetDeviceType(): void {
		$expected = DeviceTypes::ADAPTER();
		$this->entity->setDeviceType($expected);
		Assert::same($expected, $this->entity->getDeviceType());
	}

	/**
	 * Tests the function to return green LED pin number
	 */
	public function testGetGreenLedPin(): void {
		Assert::same(self::GREEN_LED_PIN, $this->entity->getGreenLedPin());
	}

	/**
	 * Tests the function to set green LED pin number
	 */
	public function testSetGreenLedPin(): void {
		$expected = 10;
		$this->entity->setGreenLedPin($expected);
		Assert::same($expected, $this->entity->getGreenLedPin());
	}

	/**
	 * Tests the function to return red LED pin number
	 */
	public function testGetRedLedPin(): void {
		Assert::same(self::RED_LED_PIN, $this->entity->getRedLedPin());
	}

	/**
	 * Tests the function to set red LED pin number
	 */
	public function testSetRedLedPin(): void {
		$expected = 15;
		$this->entity->setRedLedPin($expected);
		Assert::same($expected, $this->entity->getRedLedPin());
	}

	/**
	 * Tests the function to get button pin number
	 */
	public function testGetButtonPin(): void {
		Assert::same(self::BUTTON_PIN, $this->entity->getButtonPin());
	}

	/**
	 * Tests the function to set button pin number
	 */
	public function testSetButtonPin(): void {
		$expected = 7;
		$this->entity->setButtonPin($expected);
		Assert::same($expected, $this->entity->getButtonPin());
	}

	/**
	 * Tests the function to get I2C clock line pin number
	 */
	public function testGetSckPin(): void {
		Assert::same(self::SCK_PIN, $this->entity->getSckPin());
	}

	/**
	 * Tests the function to set I2C clock line pin number
	 */
	public function testSetSckPin(): void {
		$expected = 13;
		$this->entity->setSckPin($expected);
		Assert::same($expected, $this->entity->getSckPin());
	}

	/**
	 * Tests the function to set null I2C clock line pun
	 */
	public function testSetSckNullPin(): void {
		$this->entity->setSckPin();
		Assert::null($this->entity->getSckPin());
	}

	/**
	 * Tests the function to get I2C data line pin number
	 */
	public function testGetSdaPin(): void {
		Assert::same(self::SDA_PIN, $this->entity->getSdaPin());
	}

	/**
	 * Tests the function to set I2C data line pin number
	 */
	public function tesetSetSdaPin(): void {
		$expected = 11;
		$this->entity->setSdaPin($expected);
		Assert::same($expected, $this->entity->getSdaPin());
	}

	/**
	 * Tests the function to set null I2C data line pin
	 */
	public function testSetSdaNullPin(): void {
		$this->entity->setSdaPin();
		Assert::null($this->entity->getSdaPin());
	}

	/**
	 * Tests the function to serialize entity to JSON
	 */
	public function testJsonSerialize(): void {
		$expected = [
			'id' => null,
			'name' => self::NAME,
			'deviceType' => $this->deviceType->toScalar(),
			'greenLed' => self::GREEN_LED_PIN,
			'redLed' => self::RED_LED_PIN,
			'button' => self::BUTTON_PIN,
			'sck' => self::SCK_PIN,
			'sda' => self::SDA_PIN,
		];
		Assert::same($expected, $this->entity->jsonSerialize());
	}

}

$test = new ControllerPinConfigurationTest();
$test->run();
