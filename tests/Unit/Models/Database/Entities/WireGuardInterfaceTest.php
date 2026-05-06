<?php

/**
 * TEST: App\Models\Database\Entities\WireGuardInterface
 * @covers App\Models\Database\Entities\WireGuardInterface
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

use App\Models\Database\Entities\WireGuardInterface;
use App\Models\Database\Entities\WireGuardInterfaceIpv4;
use App\Models\Database\Entities\WireGuardInterfaceIpv6;
use App\Models\Database\Entities\WireGuardPeer;
use App\NetworkModule\Entities\MultiAddress;
use Darsyn\IP\Version\Multi;
use Doctrine\Common\Collections\ArrayCollection;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for WireGuard interface entity
 */
class WireGuardInterfaceTest extends TestCase {

	/**
	 * Interface prefix (and interface identifier value when ID = null)
	 */
	private const INTERFACE_PREFIX = 'wg_iqrf_';

	/**
	 * WireGuard interface name
	 */
	private const NAME = 'wg0';

	/**
	 * WireGuard interface private key
	 */
	private const PRIVATE_KEY = 'CHmgTLdcdr33Nr/GblDjKufGqWWxmnGv7a50hN6hZ0c=';

	/**
	 * WireGuard interface listen port
	 */
	private const PORT = 51775;

	/**
	 * @var WireGuardInterfaceIpv4 IPv4 address entity
	 */
	private WireGuardInterfaceIpv4 $ipv4Entity;

	/**
	 * @var WireGuardInterfaceIpv6 IPv6 address entity
	 */
	private WireGuardInterfaceIpv6 $ipv6Entity;

	/**
	 * @var WireGuardPeer WireGuard peer entity
	 */
	private WireGuardPeer $peerEntity;

	/**
	 * @var WireGuardInterface WireGuard interface entity
	 */
	private WireGuardInterface $entity;

	/**
	 * Tests the function to return wg interface name
	 */
	public function testGetName(): void {
		Assert::same(self::NAME, $this->entity->name);
	}

	/**
	 * Tests the function to set wg interface name
	 */
	public function testSetName(): void {
		$expected = 'testwg';
		$this->entity->name = $expected;
		Assert::same($expected, $this->entity->name);
	}

	/**
	 * Tests the function to return wg interface private key
	 */
	public function testGetPrivateKey(): void {
		Assert::same(self::PRIVATE_KEY, $this->entity->privateKey);
	}

	/**
	 * Tests the function to set wg interface private key
	 */
	public function testSetPrivateKey(): void {
		$expected = '2JEsG/gBEGZW6DyNO1c12U9XAXZYHOzHbe8jL8sLM2k=';
		$this->entity->privateKey = $expected;
		Assert::same($expected, $this->entity->privateKey);
	}

	/**
	 * Tests the function to return wg interface listen port
	 */
	public function testGetPort(): void {
		Assert::same(self::PORT, $this->entity->port);
	}

	/**
	 * Tests the function to set wg interface listen port
	 */
	public function testSetPort(): void {
		$expected = 51820;
		$this->entity->port = $expected;
		Assert::same($expected, $this->entity->port);
	}

	/**
	 * Tests the function to return wg interface ipv4 address and prefix
	 */
	public function testGetIpv4(): void {
		Assert::equal($this->ipv4Entity, $this->entity->ipv4);
	}

	/**
	 * Tests the function to set wg interface ipv4 address and prefix
	 */
	public function testSetIpv4(): void {
		$expected = new WireGuardInterfaceIpv4(new MultiAddress(Multi::factory('10.0.0.20'), 24), $this->entity);
		$this->entity->ipv4 = $expected;
		Assert::equal($expected, $this->entity->ipv4);
	}

	/**
	 * Tests the function to return wg interface ipv6 address and prefix
	 */
	public function testGetIpv6(): void {
		Assert::equal($this->ipv6Entity, $this->entity->ipv6);
	}

	/**
	 * Tests the function to set wg interface ipv6 address and prefix
	 */
	public function testSetIpv6(): void {
		$expected = new WireGuardInterfaceIpv6(new MultiAddress(Multi::factory('::20'), 48), $this->entity);
		$this->entity->ipv6 = $expected;
		Assert::equal($expected, $this->entity->ipv6);
	}

	/**
	 * Tests the function to add wg interface peer
	 */
	public function testAddPeer(): void {
		$this->entity->peers->add($this->peerEntity);
		Assert::true($this->entity->peers->contains($this->peerEntity));
	}

	/**
	 * Tests the function to remove wg interface peer
	 */
	public function testDeletePeer(): void {
		$this->entity->peers->add($this->peerEntity);
		Assert::true($this->entity->peers->contains($this->peerEntity));
		$this->entity->peers->removeElement($this->peerEntity);
		Assert::false($this->entity->peers->contains($this->peerEntity));
	}

	/**
	 * Tests the function to set wg interface peers from collection
	 */
	public function testSetPeers(): void {
		$expected = new ArrayCollection();
		Assert::equal($expected, $this->entity->peers);
		$expected->add($this->peerEntity);
		$this->entity->peers = $expected;
		Assert::equal($expected, $this->entity->peers);
	}

	/**
	 * Tests the function to serialize wg interface entity without peers and with null values into JSON
	 */
	public function testJsonSerializeNull(): void {
		$expected = [
			'id' => null,
			'name' => self::NAME,
			'port' => null,
		];
		$this->entity->port = null;
		$this->entity->ipv4 = null;
		$this->entity->ipv6 = null;
		Assert::same($expected, $this->entity->jsonSerialize());
	}

	/**
	 * Tests the function to serialize wg interface configuration into wg utility string
	 */
	public function testWgSerialize(): void {
		$expected = sprintf('wg set \'%s\' \'private-key\' \'%s\' \'listen-port\' \'%u\'', self::INTERFACE_PREFIX, self::PRIVATE_KEY, self::PORT);
		Assert::same($expected, $this->entity->wgSerialize());
	}

	/**
	 * Tests the function to serialize wg interface configuration into wg utility string with peer
	 */
	public function testWgSerializePeer(): void {
		$expected = sprintf('wg set \'%s\' \'private-key\' \'%s\' \'listen-port\' \'%u\' \'peer\' \'Z4Csw6v+89bcamtek9elXmuIEA+6PeB6CLnjNh4dJzI=\' \'endpoint\' \'vpn.example.org:51280\' \'persistent-keepalive\' \'25\' \'allowed-ips\' \'\'', self::INTERFACE_PREFIX, self::PRIVATE_KEY, self::PORT);
		$this->entity->peers->add($this->peerEntity);
		Assert::same($expected, $this->entity->wgSerialize());
	}

	/**
	 * Tests the function to create command to delete WireGuard tunnel using the ip utility
	 */
	public function testIpDelete(): void {
		$expected = 'ip link delete dev \'' . self::INTERFACE_PREFIX . '\'';
		Assert::same($expected, $this->entity->ipDelete());
	}

	/**
	 * Tests the function to create command to show WireGuard tunnel status
	 */
	public function testWgStatus(): void {
		$expected = 'wg show \'' . self::INTERFACE_PREFIX . '\'';
		Assert::same($expected, $this->entity->wgStatus());
	}

	/**
	 * Tests the function to reject invalid wg interface identifier
	 */
	public function testVerifyIdentifierError(): void {
		$expected = null;
		Assert::same($expected, $this->entity->verifyIdentifier('wrong identifier'));
	}

	/**
	 * Tests the function to reject wg interface identifier without ID (Edge case)
	 */
	public function testVerifyIdentifierNoId(): void {
		$expected = null;
		Assert::same($expected, $this->entity->verifyIdentifier(self::INTERFACE_PREFIX));
	}

	/**
	 * Tests the function to parse wg interface identifier with zero ID (Edge case)
	 */
	public function testVerifyIdentifierZeroId(): void {
		$expected = 0;
		Assert::same($expected, $this->entity->verifyIdentifier(self::INTERFACE_PREFIX . '0'));
	}

	/**
	 * Tests the function to parse wg interface identifier
	 */
	public function testVerifyIdentifierCorrect(): void {
		$expected = 11;
		Assert::same($expected, $this->entity->verifyIdentifier(self::INTERFACE_PREFIX . '11'));
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->entity = new WireGuardInterface(self::NAME, self::PRIVATE_KEY, self::PORT);
		$this->ipv4Entity = new WireGuardInterfaceIpv4(new MultiAddress(Multi::factory('192.168.1.2'), 24), $this->entity);
		$this->ipv6Entity = new WireGuardInterfaceIpv6(new MultiAddress(Multi::factory('2001:db8::'), 32), $this->entity);
		$this->entity->ipv4 = $this->ipv4Entity;
		$this->entity->ipv6 = $this->ipv6Entity;
		$this->peerEntity = new WireGuardPeer('Z4Csw6v+89bcamtek9elXmuIEA+6PeB6CLnjNh4dJzI=', null, 25, 'vpn.example.org', 51280, $this->entity);
	}

}

$test = new WireGuardInterfaceTest();
$test->run();
