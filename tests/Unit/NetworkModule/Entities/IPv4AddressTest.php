<?php

/**
 * TEST: App\NetworkModule\Entities\IPv4Address
 * @covers App\NetworkModule\Entities\IPv4Address
 * @phpVersion >= 7.2
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
final class IPv4AddressTest extends TestCase {

	/**
	 * @var IPv4 IPv4 address
	 */
	private $address;

	/**
	 * IPv4 address
	 */
	private const ADDRESS = '192.168.1.2';

	/**
	 * IPv4 network prefix
	 */
	private const PREFIX = 24;

	/**
	 * IPv4 network mask
	 */
	private const MASK = '255.255.255.0';

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
		$this->address = IPv4::factory(self::ADDRESS);
		$this->mask = IPv4::factory(self::MASK);
		$this->entity = new IPv4Address($this->address, self::PREFIX);
	}

	/**
	 * Tests the function to create a new entity from IPv4 address and subnet mask
	 */
	public function testFromMask(): void {
		Assert::equal($this->entity, IPv4Address::fromMask(self::ADDRESS, self::MASK));
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
		Assert::same(self::PREFIX, $this->entity->getPrefix());
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
			'address' => self::ADDRESS,
			'prefix' => self::PREFIX,
			'mask' => self::MASK,
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
