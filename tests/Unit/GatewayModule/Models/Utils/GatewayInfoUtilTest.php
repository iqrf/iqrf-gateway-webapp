<?php

/**
 * TEST: App\GatewayModule\Models\Utils\GatewayInfoUtil
 * @covers App\GatewayModule\Models\Utils\GatewayInfoUtil
 * @phpVersion >= 7.4
 * @testCase
 */
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

namespace Tests\Unit\GatewayModule\Models\Utils;

use App\GatewayModule\Models\Utils\GatewayInfoUtil;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for Gateway Info utility
 */
final class GatewayInfoUtilTest extends TestCase {

	/**
	 * Configuration directory path
	 */
	private const CONF_DIR = TESTER_DIR . '/data/gatewayInfo/';

	/**
	 * Gateway product
	 */
	private const GW_PRODUCT = 'IQD-GW-02A';

	/**
	 * Default gateway product
	 */
	private const DEFAULT_GW_PRODUCT = 'IQD-GW-0X';

	/**
	 * Default gateway manufacturer
	 */
	private const DEFAULT_GW_MANUFACTURER = 'MICRORISC s.r.o.';

	/**
	 * Gateway ID
	 */
	private const GW_ID = '0123456789ABCDEF';

	/**
	 * Default Gateway ID
	 */
	private const DEFAULT_GW_ID = 'FFFFFFFFFFFFFFFF';

	/**
	 * Gateway host
	 */
	private const GW_HOST = 'iqube-0123456789abcdef.local';

	/**
	 * Default gateway host
	 */
	private const DEFAULT_GW_HOST = 'iqube-ffffffffffffffff.local';

	/**
	 * Gateway image
	 */
	private const GW_IMAGE = 'iqube-armbian-v1.4.0';

	/**
	 * Default gateway image
	 */
	private const DEFAULT_GW_IMAGE = 'iqube-os-vX.Y.Z';

	/**
	 * Gateway interface
	 */
	private const GW_INTERFACE = 'spi';

	/**
	 * Default gateway interface
	 */
	private const DEFAULT_GW_INTERFACE = 'unknown';

	/**
	 * Gateway information
	 */
	private const GW_INFO = [
		'gwProduct' => self::GW_PRODUCT,
		'gwManufacturer' => self::DEFAULT_GW_MANUFACTURER,
		'gwId' => self::GW_ID,
		'gwHost' => self::GW_HOST,
		'gwImage' => self::GW_IMAGE,
		'gwInterface' => self::GW_INTERFACE,
	];

	/**
	 * Incomplete and default gateway information
	 */
	private const INCOMPLETE_GW_INFO = [
		'gwProduct' => self::GW_PRODUCT,
		'gwManufacturer' => self::DEFAULT_GW_MANUFACTURER,
		'gwId' => self::GW_ID,
		'gwHost' => self::GW_HOST,
		'gwImage' => self::DEFAULT_GW_IMAGE,
		'gwInterface' => self::GW_INTERFACE,
	];

	/**
	 * Default gateway information
	 */
	private const DEFAULT_GW_INFO = [
		'gwProduct' => self::DEFAULT_GW_PRODUCT,
		'gwManufacturer' => self::DEFAULT_GW_MANUFACTURER,
		'gwId' => self::DEFAULT_GW_ID,
		'gwHost' => self::DEFAULT_GW_HOST,
		'gwImage' => self::DEFAULT_GW_IMAGE,
		'gwInterface' => self::DEFAULT_GW_INTERFACE,
	];

	/**
	 * @var GatewayInfoUtil Gateway Info utility with correct gateway file
	 */
	private GatewayInfoUtil $gwInfo;

	/**
	 * @var GatewayInfoUtil Gateway Info utility with gateway file missing properties
	 */
	private GatewayInfoUtil $incompleteGwInfo;

	/**
	 * @var GatewayInfoUtil Gateway Info utility with gateway file containing syntax errors
	 */
	private GatewayInfoUtil $defaultGwInfo;

	/**
	 * Tests the function to read gateway info file
	 */
	public function testRead(): void {
		Assert::equal(self::GW_INFO, $this->gwInfo->read());
		Assert::equal(self::INCOMPLETE_GW_INFO, $this->incompleteGwInfo->read());
		Assert::equal(self::DEFAULT_GW_INFO, $this->defaultGwInfo->read());
	}

	/**
	 * Tests the function to get default product
	 */
	public function testGetProduct(): void {
		Assert::same(self::GW_PRODUCT, $this->gwInfo->getProduct());
		Assert::same(self::GW_PRODUCT, $this->incompleteGwInfo->getProduct());
		Assert::same(self::DEFAULT_GW_PRODUCT, $this->defaultGwInfo->getProduct());
	}

	/**
	 * Tests the function to get default manufacturer
	 */
	public function testGetManufacturer(): void {
		Assert::same(self::DEFAULT_GW_MANUFACTURER, $this->gwInfo->getManufacturer());
		Assert::same(self::DEFAULT_GW_MANUFACTURER, $this->incompleteGwInfo->getManufacturer());
		Assert::same(self::DEFAULT_GW_MANUFACTURER, $this->defaultGwInfo->getManufacturer());
	}

	/**
	 * Tests the function to get gateway ID
	 */
	public function testGetId(): void {
		Assert::same(self::GW_ID, $this->gwInfo->getId());
		Assert::same(self::GW_ID, $this->incompleteGwInfo->getId());
		Assert::same(self::DEFAULT_GW_ID, $this->defaultGwInfo->getId());
	}

	/**
	 * Tests the function to get host
	 */
	public function testGetHost(): void {
		Assert::same(self::GW_HOST, $this->gwInfo->getHost());
		Assert::same(self::GW_HOST, $this->incompleteGwInfo->getHost());
		Assert::same(self::DEFAULT_GW_HOST, $this->defaultGwInfo->getHost());
	}

	/**
	 * Tests the function to get image
	 */
	public function testGetImage(): void {
		Assert::same(self::GW_IMAGE, $this->gwInfo->getImage());
		Assert::same(self::DEFAULT_GW_IMAGE, $this->incompleteGwInfo->getImage());
		Assert::same(self::DEFAULT_GW_IMAGE, $this->defaultGwInfo->getImage());
	}

	/**
	 * Tests the function to get interface
	 */
	public function testGetInterface(): void {
		Assert::same(self::GW_INTERFACE, $this->gwInfo->getInterface());
		Assert::same(self::GW_INTERFACE, $this->incompleteGwInfo->getInterface());
		Assert::same(self::DEFAULT_GW_INTERFACE, $this->defaultGwInfo->getInterface());
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->defaultGwInfo = new GatewayInfoUtil(self::CONF_DIR . 'syntax_error/iqrf-gateway.json');
		$this->incompleteGwInfo = new GatewayInfoUtil(self::CONF_DIR . 'missing_properties/iqrf-gateway.json');
		$this->gwInfo = new GatewayInfoUtil(self::CONF_DIR . 'correct/iqrf-gateway.json');
	}

}

$test = new GatewayInfoUtilTest();
$test->run();
