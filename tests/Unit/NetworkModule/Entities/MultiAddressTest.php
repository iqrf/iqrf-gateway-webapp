<?php

/**
 * TEST: App\NetworkModule\Entities\MultiAddress
 * @covers App\NetworkModule\Entities\MultiAddress
 * @phpVersion >= 7.3
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\NetworkModule\Entities;

use App\NetworkModule\Entities\MultiAddress;
use Darsyn\IP\Version\Multi;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for MultiAddress entity
 */
final class MultiAddressTest extends TestCase {

	/**
	 * @var Multi IPv4 address
	 */
	private $ipv4;

	/**
	 * @var Multi IPv6 address
	 */
	private $ipv6;

	/**
	 * IPv4 address
	 */
	private const IPV4_ADDR = '192.168.1.2';

	/**
	 * IPv4 address prefix
	 */
	private const IPV4_PREFIX = 24;

	/**
	 * IPv6 address
	 */
	private const IPV6_ADDR = '2001:db8::';

	/**
	 * IPv6 address prefix
	 */
	private const IPV6_PREFIX = 32;

	/**
	 * @var MultiAddress IPv4 address entity
	 */
	private $ipv4Entity;

	/**
	 * @var MultiAddress IPv6 address entity
	 */
	private $ipv6Entity;

	/**
	 * Sets up the test environment
	 */
	public function __construct() {
		$this->ipv4 = Multi::factory(self::IPV4_ADDR);
		$this->ipv6 = Multi::factory(self::IPV6_ADDR);
		$this->ipv4Entity = new MultiAddress($this->ipv4, self::IPV4_PREFIX);
		$this->ipv6Entity = new MultiAddress($this->ipv6, self::IPV6_PREFIX);
	}

	/**
	 * Tests the function to get IPv4 address from multiple address type entity
	 */
	public function testGetAddressIpv4(): void {
		Assert::same($this->ipv4, $this->ipv4Entity->getAddress());
	}

	/**
	 * Tests the function to get IPv6 address from multiple address type entity
	 */
	public function testGetAddressIpv6(): void {
		Assert::same($this->ipv6, $this->ipv6Entity->getAddress());
	}

	/**
	 * Tests the function to get IPv4 address prefix from multiple address type entity
	 */
	public function testGetPrefixIpv4(): void {
		Assert::same(self::IPV4_PREFIX, $this->ipv4Entity->getPrefix());
	}

	/**
	 * Tests the function to get IPv6 address prefix from multiple address type entity
	 */
	public function testGetPrefixIpv6(): void {
		Assert::same(self::IPV6_PREFIX, $this->ipv6Entity->getPrefix());
	}

	/**
	 * Tests the function to create multiple address type entity from IPv4 address and prefix
	 */
	public function testFromPrefixIpv4(): void {
		Assert::equal($this->ipv4Entity, MultiAddress::fromPrefix('192.168.1.2/24'));
	}

	/**
	 * Tests the function to create multiple address type entity from IPv6 address and prefix
	 */
	public function testFromPrefixIpv6(): void {
		Assert::equal($this->ipv6Entity, MultiAddress::fromPrefix('2001:db8::/32'));
	}

	/**
	 * Tests the function to convert multiple address type entity to IPv4 string representation
	 */
	public function testToStringIpv4(): void {
		$expected = '192.168.1.2/24';
		Assert::same($expected, $this->ipv4Entity->toString());
	}

	/**
	 * Tests the function to convert multiple address type entity to IPv6 string representation
	 */
	public function testToStringIpv6(): void {
		$expected = '2001:db8::/32';
		Assert::same($expected, $this->ipv6Entity->toString());
	}

}

$test = new MultiAddressTest();
$test->run();
