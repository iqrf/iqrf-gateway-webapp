<?php

/**
 * TEST: App\NetworkModule\Entities\WireguardAllowedIPs
 * @covers App\NetworkModule\Entities\WireguardAllowedIPs
 * @phpVersion >= 7.3
 * @testcase
 */
declare (strict_types = 1);

namespace Tests\Unit\NetworkModule\Entities;

use App\NetworkModule\Entities\IPv4Address;
use App\NetworkModule\Entities\IPv6Address;
use App\NetworkModule\Entities\WireguardAllowedIPs;
use Darsyn\IP\Version\IPv4;
use Darsyn\IP\Version\IPv6;
use Nette\Utils\ArrayHash;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for Wireguard peer allowed IP addresses entity
 */
final class WireguardAllowedIPsTest extends TestCase {

	/**
	 * @var array<IPv4Address> $ipv4 IPv4 addresses
	 */
	private $ipv4;

	/**
	 * @var array<IPv6Address> $ipv6 IPv6 addresses
	 */
	private $ipv6;

	/**
	 * @var WireguardAllowedIPs $entity Wireguard allowed IPs entity
	 */
	private $entity;

	/**
	 * Sets up the test environment
	 */
	public function __construct() {
		$this->ipv4 = [
			new IPv4Address(IPv4::factory('192.0.2.0'), 24),
		];
		$this->ipv6 = [
			new IPv6Address(IPv6::factory('::ffff:c000:200'), 48),
		];
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->entity = new WireguardAllowedIPs($this->ipv4, $this->ipv6);
	}

	/**
	 * Tests the function to deserialize Wireguard peer allowed IPs from JSON to entity
	 */
	public function testJsonDeserialize(): void {
		$arrayHash = ArrayHash::from([
			'ipv4' => [
				[
					'address' => '192.0.2.0',
					'prefix' => 24,
				],
			],
			'ipv6' => [
				[
					'address' => '::ffff:c000:200',
					'prefix' => 48,
				],
			],
		], true);
		$ipv4 = [
			new IPv4Address(IPv4::factory('192.0.2.0'), 24),
		];
		$ipv6 = [
			new IPv6Address(IPv6::factory('::ffff:c000:200'), 48),
		];
		$expected = new WireguardAllowedIPs($ipv4, $ipv6);
		$actual = WireguardAllowedIPs::jsonDeserialize($arrayHash);
		Assert::equal($expected, $actual);
	}

	/**
	 * Tests the function to serialize Wireguard peer allowed IPs from entity into json
	 */
	public function testJsonSerialize(): void {
		$expected = [
			'ipv4' => [
				[
					'address' => '192.0.2.0',
					'prefix' => 24,
				],
			],
			'ipv6' => [
				[
					'address' => '::ffff:c000:200',
					'prefix' => 48,
				],
			],
		];
		Assert::same($expected, $this->entity->jsonSerialize());
	}

	/**
	 * Tests the function to serialize Wireguard peer allowed IPs from entity into wg utility command
	 */
	public function testWgSerialize(): void {
		$expected = 'allowed-ips 192.0.2.0/24,::ffff:c000:200/48';
		Assert::same($expected, $this->entity->wgSerialize());
	}

	/**
	 * Tests the function to convert Wireguard peer allowed IPs entity into configuration format
	 */
	public function testToConf(): void {
		$expected = 'AllowedIPs = 192.0.2.0/24,::ffff:c000:200/48';
		Assert::same($expected, $this->entity->toConf());
	}

}

$test = new WireguardAllowedIPsTest();
$test->run();
