<?php

/**
 * TEST: App\NetworkModule\Entities\IPv6Address
 * @covers App\NetworkModule\Entities\IPv6Address
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\NetworkModule\Entities;

use App\NetworkModule\Entities\IPv6Address;
use Darsyn\IP\Version\IPv6;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for network connection entity
 */
class IPv6AddressTest extends TestCase {

	/**
	 * @var IPv6 IPv6 address
	 */
	private $address;

	/**
	 * @var int IPv6 prefix
	 */
	private $prefix = 112;

	/**
	 * @var IPv6 IPv6 gateway address
	 */
	private $gateway;

	/**
	 * @var IPv6Address IPv6 address entity
	 */
	private $entity;

	/**
	 * Sets up the test environment
	 */
	public function __construct() {
		$this->address = IPv6::factory('2a00:19a0:3:75:0:d9c6:75a0:1');
		$this->gateway = IPv6::factory('fe80::1');
		$this->entity = new IPv6Address($this->address, $this->prefix, $this->gateway);
	}

	/**
	 * Tests the function to create the entity from IPv6 address with prefix
	 */
	public function testFromPrefix(): void {
		Assert::equal($this->entity, IPv6Address::fromPrefix('2a00:19a0:3:75:0:d9c6:75a0:1/112', 'fe80::1'));
	}

	/**
	 * Tests the function to get IPv6 address
	 */
	public function testGetAddress(): void {
		Assert::same($this->address, $this->entity->getAddress());
	}

	/**
	 * Tests the function to get IPv6 prefix
	 */
	public function testGetPrefix(): void {
		Assert::same($this->prefix, $this->entity->getPrefix());
	}

	/**
	 * Tests the function to get IPv6 gateway address
	 */
	public function testGetGateway(): void {
		Assert::same($this->gateway, $this->entity->getGateway());
	}

	/**
	 * Tests the function to convert IPv6 address entity to an array
	 */
	public function testToArray(): void {
		$expected = [
			'address' => '2a00:19a0:3:75::d9c6:75a0:1',
			'prefix' => 112,
			'gateway' => 'fe80::1',
		];
		Assert::same($expected, $this->entity->toArray());
	}

	/**
	 * Tests the function to convert IPv6 address entity to a string
	 */
	public function testToString(): void {
		$expected = '2a00:19a0:3:75::d9c6:75a0:1/112';
		Assert::same($expected, $this->entity->toString());
	}

}

$test = new IPv6AddressTest();
$test->run();
