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
/**
 * TEST: App\Models\Database\Entities\WireguardInterfaceIpv6
 * @covers App\Models\Database\Entities\WireguardInterfaceIpv6
 * @phpVersion >= 7.3
 * @testCase
 */

declare(strict_types = 1);

namespace Tests\Unit\Models\Database\Entities;

use App\Models\Database\Entities\WireguardInterface;
use App\Models\Database\Entities\WireguardInterfaceIpv6;
use App\NetworkModule\Entities\MultiAddress;
use Darsyn\IP\Version\Multi;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for wireguard interface entity
 */
class WireguardInterfaceIpv6Test extends TestCase {

	/**
	 * IPv6 address
	 */
	private const ADDRESS = '2001:db8::';

	/**
	 * IPv6 address prefix
	 */
	private const PREFIX = 32;

	/**
	 * @var WireguardInterface Wireguard interface entity
	 */
	private $interfaceEntity;

	/**
	 * @var WireguardInterfaceIpv6 Wireguard interface IPv4 entity
	 */
	private $entity;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->interfaceEntity = new WireguardInterface('wg0', 'CHmgTLdcdr33Nr/GblDjKufGqWWxmnGv7a50hN6hZ0c=', 51775);
		$this->entity = new WireguardInterfaceIpv6(new MultiAddress(Multi::factory(self::ADDRESS), self::PREFIX), $this->interfaceEntity);
	}

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
		$expected = new MultiAddress(Multi::factory('2001:db8::'), 24);
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
		Assert::same('2001:db8::/32', $this->entity->toString());
	}

}

$test = new WireguardInterfaceIpv6Test();
$test->run();
