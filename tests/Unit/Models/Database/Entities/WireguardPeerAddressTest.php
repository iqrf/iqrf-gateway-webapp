<?php

/**
 * TEST: App\Models\Database\Entities\WireguardPeerAddress
 * @covers App\Models\Database\Entities\WireguardPeerAddress
 * @phpVersion >= 7.3
 * @testCase
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
 * Tests for wireguard peer address entity
 */
final class WireguardPeerAddressTest extends TestCase {

	/**
	 * @var MultiAddress IPv4 address entity
	 */
	private $ipv4Entity;

	/**
	 * @var MultiAddress IPv6 address entity
	 */
	private $ipv6Entity;

	/**
	 * @var WireguardInterface Wireguard interface entity
	 */
	private $interfaceEntity;

	/**
	 * @var WireguardPeer Wireguard peer entity
	 */
	private $peerEntity;

	/**
	 * @var WireguardPeerAddress Wireguard peer address entity
	 */
	private $entity;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->interfaceEntity = $this->interfaceEntity = new WireguardInterface('wg0', 'CHmgTLdcdr33Nr/GblDjKufGqWWxmnGv7a50hN6hZ0c=', null, null, null);
		$this->peerEntity = new WireguardPeer('Z4Csw6v+89bcamtek9elXmuIEA+6PeB6CLnjNh4dJzI=', null, 25, 'vpn.example.org', 51280, $this->interfaceEntity);
		$this->ipv4Entity = new MultiAddress(Multi::factory('192.168.1.2'), 24);
		$this->ipv6Entity = new MultiAddress(Multi::factory('::'), 48);
		$this->entity = new WireguardPeerAddress($this->ipv4Entity, $this->peerEntity);
	}

	/**
	 * Tests the function to get wireguard peer address entity
	 */
	public function testGetAddress(): void {
		Assert::equal($this->ipv4Entity, $this->entity->getAddress());
	}

	/**
	 * Tests the function to set wireguard peer address entity
	 */
	public function testSetAddress(): void {
		$this->entity->setAddress($this->ipv6Entity);
		Assert::equal($this->ipv6Entity, $this->entity->getAddress());
	}

	/**
	 * Tests the function to return wireguard address peer entity
	 */
	public function testGetPeer(): void {
		Assert::equal($this->peerEntity, $this->entity->getPeer());
	}

	/**
	 * Tests the function to set wireguard address peer entity
	 */
	public function testSetPeer(): void {
		$expected = new WireguardPeer('Z4Csw6v+89bcamtek9elXmuIEA+6PeB6CLnjNh4dJzI=', null, 30, 'vpn.test.org', 51281, $this->interfaceEntity);
		$this->entity->setPeer($expected);
		Assert::equal($expected, $this->entity->getPeer());
	}

	/**
	 * Tests the function to serialize wireguard peer ipv4 address entity into JSON
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
	 * Tests the function to serialize wireguard peer ipv6 address entity
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

}

$test = new WireguardPeerAddressTest();
$test->run();
