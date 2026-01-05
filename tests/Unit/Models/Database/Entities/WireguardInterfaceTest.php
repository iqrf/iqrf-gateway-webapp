<?php

/**
 * TEST: App\Models\Database\Entities\WireguardInterface
 * @covers App\Models\Database\Entities\WireguardInterface
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
use App\Models\Database\Entities\WireguardInterfaceIpv4;
use App\Models\Database\Entities\WireguardInterfaceIpv6;
use App\Models\Database\Entities\WireguardPeer;
use App\NetworkModule\Entities\MultiAddress;
use Darsyn\IP\Version\Multi;
use Doctrine\Common\Collections\ArrayCollection;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for WireGuard interface entity
 */
class WireguardInterfaceTest extends TestCase {

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
	 * @var WireguardInterfaceIpv4 IPv4 address entity
	 */
	private WireguardInterfaceIpv4 $ipv4Entity;

	/**
	 * @var WireguardInterfaceIpv6 IPv6 address entity
	 */
	private WireguardInterfaceIpv6 $ipv6Entity;

	/**
	 * @var WireguardPeer WireGuard peer entity
	 */
	private WireguardPeer $peerEntity;

	/**
	 * @var WireguardInterface WireGuard interface entity
	 */
	private WireguardInterface $entity;

	/**
	 * Tests the function to return wg interface name
	 */
	public function testGetName(): void {
		Assert::same(self::NAME, $this->entity->getName());
	}

	/**
	 * Tests the function to set wg interface name
	 */
	public function testSetName(): void {
		$expected = 'testwg';
		$this->entity->setName($expected);
		Assert::same($expected, $this->entity->getName());
	}

	/**
	 * Tests the function to return wg interface private key
	 */
	public function testGetPrivateKey(): void {
		Assert::same(self::PRIVATE_KEY, $this->entity->getPrivateKey());
	}

	/**
	 * Tests the function to set wg interface private key
	 */
	public function testSetPrivateKey(): void {
		$expected = '2JEsG/gBEGZW6DyNO1c12U9XAXZYHOzHbe8jL8sLM2k=';
		$this->entity->setPrivateKey($expected);
		Assert::same($expected, $this->entity->getPrivateKey());
	}

	/**
	 * Tests the function to return wg interface listen port
	 */
	public function testGetPort(): void {
		Assert::same(self::PORT, $this->entity->getPort());
	}

	/**
	 * Tests the function to set wg interface listen port
	 */
	public function testSetPort(): void {
		$expected = 51820;
		$this->entity->setPort($expected);
		Assert::same($expected, $this->entity->getPort());
	}

	/**
	 * Tests the function to clear wg interface listen port
	 */
	public function testSetPortNull(): void {
		$this->entity->setPort();
		Assert::null($this->entity->getPort());
	}

	/**
	 * Tests the function to return wg interface ipv4 address and prefix
	 */
	public function testGetIpv4(): void {
		Assert::equal($this->ipv4Entity, $this->entity->getIpv4());
	}

	/**
	 * Tests the function to set wg interface ipv4 address and prefix
	 */
	public function testSetIpv4(): void {
		$expected = new WireguardInterfaceIpv4(new MultiAddress(Multi::factory('10.0.0.20'), 24), $this->entity);
		$this->entity->setIpv4($expected);
		Assert::equal($expected, $this->entity->getIpv4());
	}

	/**
	 * Tests the function to clear wg interface ipv4 address and prefix
	 */
	public function testSetIpv4Null(): void {
		$this->entity->setIpv4();
		Assert::null($this->entity->getIpv4());
	}

	/**
	 * Tests the function to return wg interface ipv6 address and prefix
	 */
	public function testGetIpv6(): void {
		Assert::equal($this->ipv6Entity, $this->entity->getIpv6());
	}

	/**
	 * Tests the function to set wg interface ipv6 address and prefix
	 */
	public function testSetIpv6(): void {
		$expected = new WireguardInterfaceIpv6(new MultiAddress(Multi::factory('::20'), 48), $this->entity);
		$this->entity->setIpv6($expected);
		Assert::equal($expected, $this->entity->getIpv6());
	}

	/**
	 * Tests the function to clear wg interface ipv6 address and prefix
	 */
	public function testSetIpv6Null(): void {
		$this->entity->setIpv6();
		Assert::null($this->entity->getIpv6());
	}

	/**
	 * Tests the function to add wg interface peer
	 */
	public function testAddPeer(): void {
		$this->entity->addPeer($this->peerEntity);
		Assert::true($this->entity->getPeers()->contains($this->peerEntity));
	}

	/**
	 * Tests the function to remove wg interface peer
	 */
	public function testDeletePeer(): void {
		$this->entity->addPeer($this->peerEntity);
		Assert::true($this->entity->getPeers()->contains($this->peerEntity));
		$this->entity->deletePeer($this->peerEntity);
		Assert::false($this->entity->getPeers()->contains($this->peerEntity));
	}

	/**
	 * Tests the function to set wg interface peers from collection
	 */
	public function testSetPeers(): void {
		$expected = new ArrayCollection();
		Assert::equal($expected, $this->entity->getPeers());
		$expected->add($this->peerEntity);
		$this->entity->setPeers($expected);
		Assert::equal($expected, $this->entity->getPeers());
	}

	/**
	 * Tests the function to serialize wg interface entity without peers and with null values into JSON
	 */
	public function testJsonSerializeNull(): void {
		$expected = [
			'id' => null,
			'name' => self::NAME,
			'privateKey' => self::PRIVATE_KEY,
			'port' => null,
		];
		$this->entity->setPort();
		$this->entity->setIpv4();
		$this->entity->setIpv6();
		Assert::same($expected, $this->entity->jsonSerialize());
	}

	/**
	 * Tests the function to serialize wg interface configuration into wg utility string
	 */
	public function testWgSerialize(): void {
		$expected = sprintf('wg set \'%s\' \'private-key\' \'%s\' \'listen-port\' \'%u\'', self::NAME, self::PRIVATE_KEY, self::PORT);
		Assert::same($expected, $this->entity->wgSerialize());
	}

	/**
	 * Tests the function to serialize wg interface configuration into wg utility string with peer
	 */
	public function testWgSerializePeer(): void {
		$expected = sprintf('wg set \'%s\' \'private-key\' \'%s\' \'listen-port\' \'%u\' \'peer\' \'Z4Csw6v+89bcamtek9elXmuIEA+6PeB6CLnjNh4dJzI=\' \'endpoint\' \'vpn.example.org:51280\' \'persistent-keepalive\' \'25\' \'allowed-ips\' \'\'', self::NAME, self::PRIVATE_KEY, self::PORT);
		$this->entity->addPeer($this->peerEntity);
		Assert::same($expected, $this->entity->wgSerialize());
	}

	/**
	 * Tests the function to create command to delete WireGuard tunnel using the ip utility
	 */
	public function testIpDelete(): void {
		$expected = 'ip link delete dev \'' . self::NAME . '\'';
		Assert::same($expected, $this->entity->ipDelete());
	}

	/**
	 * Tests the function to create command to show WireGuard tunnel status
	 */
	public function testWgStatus(): void {
		$expected = 'wg show \'' . self::NAME . '\'';
		Assert::same($expected, $this->entity->wgStatus());
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->entity = new WireguardInterface(self::NAME, self::PRIVATE_KEY, self::PORT);
		$this->ipv4Entity = new WireguardInterfaceIpv4(new MultiAddress(Multi::factory('192.168.1.2'), 24), $this->entity);
		$this->ipv6Entity = new WireguardInterfaceIpv6(new MultiAddress(Multi::factory('2001:db8::'), 32), $this->entity);
		$this->entity->setIpv4($this->ipv4Entity);
		$this->entity->setIpv6($this->ipv6Entity);
		$this->peerEntity = new WireguardPeer('Z4Csw6v+89bcamtek9elXmuIEA+6PeB6CLnjNh4dJzI=', null, 25, 'vpn.example.org', 51280, $this->entity);
	}

}

$test = new WireguardInterfaceTest();
$test->run();
