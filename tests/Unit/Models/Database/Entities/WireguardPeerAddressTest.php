<?php

/**
 * TEST: App\Models\Database\Entities\WireguardPeerAddress
 * @covers App\Models\Database\Entities\WireguardPeerAddress
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

use App\Models\Database\Entities\WireguardInterface;
use App\Models\Database\Entities\WireguardPeer;
use App\Models\Database\Entities\WireguardPeerAddress;
use App\NetworkModule\Entities\MultiAddress;
use Darsyn\IP\Version\Multi;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for WireGuard peer address entity
 */
final class WireguardPeerAddressTest extends TestCase {

	/**
	 * @var MultiAddress IPv4 address entity
	 */
	private MultiAddress $ipv4Entity;

	/**
	 * @var MultiAddress IPv6 address entity
	 */
	private MultiAddress $ipv6Entity;

	/**
	 * @var WireguardInterface WireGuard interface entity
	 */
	private WireguardInterface $interfaceEntity;

	/**
	 * @var WireguardPeer WireGuard peer entity
	 */
	private WireguardPeer $peerEntity;

	/**
	 * @var WireguardPeerAddress WireGuard peer address entity
	 */
	private WireguardPeerAddress $entity;

	/**
	 * Tests the function to get WireGuard peer address entity
	 */
	public function testGetAddress(): void {
		Assert::equal($this->ipv4Entity, $this->entity->getAddress());
	}

	/**
	 * Tests the function to set WireGuard peer address entity
	 */
	public function testSetAddress(): void {
		$this->entity->setAddress($this->ipv6Entity);
		Assert::equal($this->ipv6Entity, $this->entity->getAddress());
	}

	/**
	 * Tests the function to return WireGuard address peer entity
	 */
	public function testGetPeer(): void {
		Assert::equal($this->peerEntity, $this->entity->getPeer());
	}

	/**
	 * Tests the function to set WireGuard address peer entity
	 */
	public function testSetPeer(): void {
		$expected = new WireguardPeer('Z4Csw6v+89bcamtek9elXmuIEA+6PeB6CLnjNh4dJzI=', null, 30, 'vpn.test.org', 51281, $this->interfaceEntity);
		$this->entity->setPeer($expected);
		Assert::equal($expected, $this->entity->getPeer());
	}

	/**
	 * Tests the function to serialize WireGuard peer ipv4 address entity into JSON
	 */
	public function testJsonSerializeIpv4(): void {
		$expected = [
			'id' => null,
			'address' => '192.168.1.2',
			'prefix' => 24,
		];
		Assert::same($expected, $this->entity->jsonSerialize());
	}

	/**
	 * Tests the function to serialize WireGuard peer ipv6 address entity
	 */
	public function testJsonSerializeIpv6(): void {
		$expected = [
			'id' => null,
			'address' => '::',
			'prefix' => 48,
		];
		$this->entity->setAddress($this->ipv6Entity);
		Assert::same($expected, $this->entity->jsonSerialize());
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->interfaceEntity = new WireguardInterface('wg0', 'CHmgTLdcdr33Nr/GblDjKufGqWWxmnGv7a50hN6hZ0c=', null);
		$this->peerEntity = new WireguardPeer('Z4Csw6v+89bcamtek9elXmuIEA+6PeB6CLnjNh4dJzI=', null, 25, 'vpn.example.org', 51280, $this->interfaceEntity);
		$this->ipv4Entity = new MultiAddress(Multi::factory('192.168.1.2'), 24);
		$this->ipv6Entity = new MultiAddress(Multi::factory('::'), 48);
		$this->entity = new WireguardPeerAddress($this->ipv4Entity, $this->peerEntity);
	}

}

$test = new WireguardPeerAddressTest();
$test->run();
