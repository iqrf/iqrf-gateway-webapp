<?php

/**
 * TEST: App\Models\Database\Entities\WireguardPeer
 * @covers App\Models\Database\Entities\WireguardPeer
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
use Doctrine\Common\Collections\ArrayCollection;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for WireGuard peer entity
 */
final class WireguardPeerTest extends TestCase {

	/**
	 * WireGuard peer public key
	 */
	private const PUBLIC_KEY = 'Z4Csw6v+89bcamtek9elXmuIEA+6PeB6CLnjNh4dJzI=';

	/**
	 * WireGuard peer pre-shared key
	 */
	private const PSK = 'oC9WMZJs56UDp7NU2j8KfBn01zDLPRW2hGxivWC7Rhg=';

	/**
	 * WireGuard peer keepalive interval
	 */
	private const KEEPALIVE = 25;

	/**
	 * WireGuard peer endpoint
	 */
	private const ENDPOINT = 'vpn.example.org';

	/**
	 * WireGuard peer listen port
	 */
	private const PORT = 51820;

	/**
	 * @var WireguardPeerAddress WireGuard peer IPv4 address entity
	 */
	private WireguardPeerAddress $peerIpv4Entity;

	/**
	 * @var WireguardPeerAddress WireGuard peer IPv6 address entity
	 */
	private WireguardPeerAddress $peerIpv6Entity;

	/**
	 * @var WireguardInterface WireGuard interface entity
	 */
	private WireguardInterface $interfaceEntity;

	/**
	 * @var WireguardPeer WireGuard peer entity
	 */
	private WireguardPeer $entity;

	/**
	 * Tests the function to get wg peer public key
	 */
	public function testGetPublicKey(): void {
		Assert::same(self::PUBLIC_KEY, $this->entity->getPublicKey());
	}

	/**
	 * Tests the function to set wg peer public key
	 */
	public function testSetPublicKey(): void {
		$expected = 'cLHdkQ7mrTd8zRIPJqbFyBhVA9S62VE1u0vEPwupA08=';
		$this->entity->setPublicKey($expected);
		Assert::same($expected, $this->entity->getPublicKey());
	}

	/**
	 * Tests the function to get wg peer pre-shared key
	 */
	public function testGetPsk(): void {
		Assert::same(self::PSK, $this->entity->getPsk());
	}

	/**
	 * Tests the function to set wg peer pre-shared key
	 */
	public function testSetPsk(): void {
		$expected = 'g8iHvarFzk4gCpjdOYglztEKKxsxEyn5m2hRduBMu4U=';
		$this->entity->setPsk($expected);
		Assert::same($expected, $this->entity->getPsk());
	}

	/**
	 * Tests the function to clear wg peer pre-shared key
	 */
	public function testSetPskNull(): void {
		$this->entity->setPsk();
		Assert::null($this->entity->getPsk());
	}

	/**
	 * Tests the function to get wg peer keepalive interval
	 */
	public function testGetKeepalive(): void {
		Assert::same(self::KEEPALIVE, $this->entity->getKeepalive());
	}

	/**
	 * Tests the function to set wg peer keepalive interval
	 */
	public function testSetKeepalive(): void {
		$expected = 30;
		$this->entity->setKeepalive($expected);
		Assert::same($expected, $this->entity->getKeepalive());
	}

	/**
	 * Tests the function to get wg peer endpoint
	 */
	public function testGetEndpoint(): void {
		Assert::same(self::ENDPOINT, $this->entity->getEndpoint());
	}

	/**
	 * Tests the function to set wg peer endpoint
	 */
	public function testSetEndpoint(): void {
		$expected = 'vpn.test.org';
		$this->entity->setEndpoint($expected);
		Assert::same($expected, $this->entity->getEndpoint());
	}

	/**
	 * Tests the function to get wg peer listen port
	 */
	public function testGetPort(): void {
		Assert::same(self::PORT, $this->entity->getPort());
	}

	/**
	 * Tests the function to set wg peer listen port
	 */
	public function testSetPort(): void {
		$expected = 51821;
		$this->entity->setPort($expected);
		Assert::same($expected, $this->entity->getPort());
	}

	/**
	 * Tests the function to get wg peer interface
	 */
	public function testGetInterface(): void {
		Assert::equal($this->interfaceEntity, $this->entity->getInterface());
	}

	/**
	 * Tests the function to set wg peer interface
	 */
	public function testSetInterface(): void {
		$expected = new WireguardInterface('test', 'CHmgTLdcdr33Nr/GblDjKufGqWWxmnGv7abcdef1234=', null);
		$this->entity->setInterface($expected);
		Assert::equal($expected, $this->entity->getInterface());
	}

	/**
	 * Tests the function to add wg peer allowed IP
	 */
	public function testAddAddress(): void {
		$this->entity->addAddress($this->peerIpv4Entity);
		Assert::true($this->entity->getAddresses()->contains($this->peerIpv4Entity));
	}

	/**
	 * Tests the function to delete wg peer allowed IP
	 */
	public function testDeleteAddress(): void {
		$this->entity->addAddress($this->peerIpv4Entity);
		Assert::true($this->entity->getAddresses()->contains($this->peerIpv4Entity));
		$this->entity->deleteAddress($this->peerIpv4Entity);
		Assert::true(!$this->entity->getAddresses()->contains($this->peerIpv4Entity));
	}

	/**
	 * Tests the function to set wg peer allowed IPs from collection
	 */
	public function testSetAddresses(): void {
		$expected = new ArrayCollection();
		Assert::equal($expected, $this->entity->getAddresses());
		$expected->add($this->peerIpv6Entity);
		$this->entity->setAddresses($expected);
		Assert::equal($expected, $this->entity->getAddresses());
	}

	/**
	 * Tests the function to serialize wg peer entity into JSON
	 */
	public function testJsonSerialize(): void {
		$expected = [
			'id' => null,
			'publicKey' => self::PUBLIC_KEY,
			'psk' => self::PSK,
			'keepalive' => self::KEEPALIVE,
			'endpoint' => self::ENDPOINT,
			'port' => self::PORT,
			'allowedIPs' => [
				'ipv4' => [
					[
						'id' => null,
						'address' => $this->peerIpv4Entity->getAddress()->getAddress()->getDotAddress(),
						'prefix' => $this->peerIpv4Entity->getAddress()->getPrefix(),
					],
				],
				'ipv6' => [
					[
						'id' => null,
						'address' => $this->peerIpv6Entity->getAddress()->getAddress()->getCompactedAddress(),
						'prefix' => $this->peerIpv6Entity->getAddress()->getPrefix(),
					],
				],
			],
		];
		$this->entity->addAddress($this->peerIpv4Entity);
		$this->entity->addAddress($this->peerIpv6Entity);
		Assert::same($expected, $this->entity->jsonSerialize());
	}

	/**
	 * Tests the function to serialize wg peer configuration into wg utility string
	 */
	public function testWgSerialize(): void {
		$this->entity->addAddress($this->peerIpv4Entity);
		$this->entity->addAddress($this->peerIpv6Entity);
		$expected = sprintf('\'peer\' \'%s\' \'preshared-key\' \'%s\' \'endpoint\' \'%s:%u\' \'persistent-keepalive\' \'%u\' \'allowed-ips\' \'10.0.0.0/32,::/48\'', self::PUBLIC_KEY, self::PSK, self::ENDPOINT, self::PORT, self::KEEPALIVE);
		Assert::same($expected, $this->entity->wgSerialize());
	}

	/**
	 * Sets the test environment
	 */
	protected function setUp(): void {
		$this->interfaceEntity = new WireguardInterface('wg0', 'CHmgTLdcdr33Nr/GblDjKufGqWWxmnGv7a50hN6hZ0c=', null);
		$this->entity = new WireguardPeer(self::PUBLIC_KEY, self::PSK, self::KEEPALIVE, self::ENDPOINT, self::PORT, $this->interfaceEntity);
		$this->peerIpv4Entity = new WireguardPeerAddress(new MultiAddress(Multi::factory('10.0.0.0'), 32), $this->entity);
		$this->peerIpv6Entity = new WireguardPeerAddress(new MultiAddress(Multi::factory('::'), 48), $this->entity);
	}

}

$test = new WireguardPeerTest();
$test->run();
