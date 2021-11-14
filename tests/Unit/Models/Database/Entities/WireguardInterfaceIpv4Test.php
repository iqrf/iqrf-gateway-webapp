<?php

/**
 * TEST: App\Models\Database\Entities\WireguardInterfaceIpv4
 * @covers App\Models\Database\Entities\WireguardInterfaceIpv4
 * @phpVersion >= 7.3
 * @testCase
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
 * Tests for wireguard interface entity
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
	 * @var WireguardInterface Wireguard interface entity
	 */
	private $interfaceEntity;

	/**
	 * @var WireguardInterfaceIpv4 Wireguard interface IPv4 entity
	 */
	private $entity;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->interfaceEntity = new WireguardInterface('wg0', 'CHmgTLdcdr33Nr/GblDjKufGqWWxmnGv7a50hN6hZ0c=', 51775);
		$this->entity = new WireguardInterfaceIpv4(new MultiAddress(Multi::factory(self::ADDRESS), self::PREFIX), $this->interfaceEntity);
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

}

$test = new WireguardInterfaceIpv4Test();
$test->run();
