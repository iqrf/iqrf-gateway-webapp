<?php

/**
 * TEST: App\Models\Database\Entities\WireguardInterfaceIpv4
 * @covers App\Models\Database\Entities\WireguardInterfaceIpv4
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

namespace Tests\Unit\Models\Database\Entities;

use App\Models\Database\Entities\WireguardInterface;
use App\Models\Database\Entities\WireguardInterfaceIpv4;
use App\NetworkModule\Entities\MultiAddress;
use Darsyn\IP\Version\Multi;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for WireGuard interface entity
 */
class WireguardInterfaceIpv4Test extends TestCase {

	/**
	 * IPv4 address
	 */
	private const ADDRESS = '192.168.1.2';

	/**
	 * IPv4 address prefix
	 */
	private const PREFIX = 24;

	/**
	 * @var WireguardInterface WireGuard interface entity
	 */
	private WireguardInterface $interfaceEntity;

	/**
	 * @var WireguardInterfaceIpv4 WireGuard interface IPv4 entity
	 */
	private WireguardInterfaceIpv4 $entity;

	/**
	 * Tests the function to get address entity
	 */
	public function testGetAddress(): void {
		$expected = new MultiAddress(Multi::factory(self::ADDRESS), self::PREFIX);
		Assert::equal($expected, $this->entity->getAddress());
	}

	/**
	 * Tests the function to set address entity
	 */
	public function testSetAddress(): void {
		$expected = new MultiAddress(Multi::factory('192.168.0.101'), 24);
		$this->entity->setAddress($expected);
		Assert::equal($expected, $this->entity->getAddress());
	}

	/**
	 * Tests the function to get interface entity
	 */
	public function testGetInterface(): void {
		Assert::equal($this->interfaceEntity, $this->entity->getInterface());
	}

	/**
	 * Tests the function to set interface entity
	 */
	public function testSetInterface(): void {
		$expected = new WireguardInterface('wg1', 'CHmgTLdcdr33Nr/GblDjKufGqWWxmnGv7a50hN6hZ0b=', 51820);
		$this->entity->setInterface($expected);
		Assert::equal($expected, $this->entity->getInterface());
	}

	/**
	 * Tests the function to serialize IPv4 entity into JSON
	 */
	public function testJsonSerialize(): void {
		$expected = [
			'id' => null,
			'address' => self::ADDRESS,
			'prefix' => self::PREFIX,
		];
		Assert::same($expected, $this->entity->jsonSerialize());
	}

	/**
	 * Tests the function to convert entity into string representation of IPv4 address
	 */
	public function testToString(): void {
		Assert::same('192.168.1.2/24', $this->entity->toString());
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->interfaceEntity = new WireguardInterface('wg0', 'CHmgTLdcdr33Nr/GblDjKufGqWWxmnGv7a50hN6hZ0c=', 51775);
		$this->entity = new WireguardInterfaceIpv4(new MultiAddress(Multi::factory(self::ADDRESS), self::PREFIX), $this->interfaceEntity);
	}

}

$test = new WireguardInterfaceIpv4Test();
$test->run();
