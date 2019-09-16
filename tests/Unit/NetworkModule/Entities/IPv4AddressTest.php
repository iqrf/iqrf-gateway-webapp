<?php

/**
 * TEST: App\NetworkModule\Entities\IPv4Address
 * @covers App\NetworkModule\Entities\IPv4Address
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\NetworkModule\Entities;

use App\NetworkModule\Entities\IPv4Address;
use Darsyn\IP\Version\IPv4;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for network connection entity
 */
class IPv4AddressTest extends TestCase {

	/**
	 * @var IPv4 IPv4 address
	 */
	private $address;

	/**
	 * @var int IPv4 prefix
	 */
	private $prefix = 24;

	/**
	 * @var IPv4 IPv4 mask
	 */
	private $mask;

	/**
	 * @var IPv4Address IPv4 address entity
	 */
	private $entity;

	/**
	 * Sets up the test environment
	 */
	public function __construct() {
		$this->address = IPv4::factory('192.168.1.2');
		$this->mask = IPv4::factory('255.255.255.0');
		$this->entity = new IPv4Address($this->address, $this->prefix);
	}

	/**
	 * Tests the function to create a new entity from IPv4 address and subnet mask
	 */
	public function testFromMask(): void {
		Assert::equal($this->entity, IPv4Address::fromMask('192.168.1.2', '255.255.255.0'));
	}

	/**
	 * Tests the function to create the entity from IPv4 address with prefix
	 */
	public function testFromPrefix(): void {
		Assert::equal($this->entity, IPv4Address::fromPrefix('192.168.1.2/24'));
	}

	/**
	 * Tests the function to get IPv4 address
	 */
	public function testGetAddress(): void {
		Assert::same($this->address, $this->entity->getAddress());
	}

	/**
	 * Tests the function to get IPv4 prefix
	 */
	public function testGetPrefix(): void {
		Assert::same($this->prefix, $this->entity->getPrefix());
	}

	/**
	 * Tests the function to get IPv4 mask
	 */
	public function testGetMask(): void {
		Assert::equal($this->mask, $this->entity->getMask());
		$address = new IPv4Address(IPv4::factory('192.168.1.1'), 32);
		Assert::equal(IPv4::factory('255.255.255.255'), $address->getMask());
	}

	/**
	 * Tests the function to convert the IPv4 address entity to an array
	 */
	public function testToArray(): void {
		$expected = [
			'address' => '192.168.1.2',
			'prefix' => 24,
			'mask' => '255.255.255.0',
		];
		Assert::same($expected, $this->entity->toArray());
	}

	/**
	 * Tests the function to convert the IPv4 address entity to a string
	 */
	public function testToString(): void {
		$expected = '192.168.1.2/24';
		Assert::same($expected, $this->entity->toString());
	}

}

$test = new IPv4AddressTest();
$test->run();
