<?php

/**
 * TEST: App\NetworkModule\Entities\IPv6Connection
 * @covers App\NetworkModule\Entities\IPv6Connection
 * @phpVersion >= 7.2
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\NetworkModule\Entities;

use App\NetworkModule\Entities\IPv6Address;
use App\NetworkModule\Entities\IPv6Connection;
use App\NetworkModule\Enums\IPv6Methods;
use Darsyn\IP\Version\IPv6;
use Nette\Utils\ArrayHash;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for network connection entity
 */
final class IPv6ConnectionTest extends TestCase {

	/**
	 * NetworkManager data directory
	 */
	private const NM_DATA = __DIR__ . '/../../../data/networkManager/';

	/**
	 * @var IPv6Methods IPv6 connection method
	 */
	private $method;

	/**
	 * @var array<IPv6Address> IPv6 addresses
	 */
	private $addresses;

	/**
	 * @var array<IPv6> IPv6 addresses of DNS servers
	 */
	private $dns;

	/**
	 * @var IPv6Connection IPv6 connection entity
	 */
	private $entity;

	/**
	 * Sets up the test environment
	 */
	public function __construct() {
		$this->method = IPv6Methods::MANUAL();
		$this->addresses = [
			new IPv6Address(IPv6::factory('2001:470:5bb2::2'), 64, IPv6::factory('fe80::1')),
		];
		$this->dns = [
			IPv6::factory('2001:470:5bb2::1'),
		];
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void  {
		$this->entity = new IPv6Connection($this->method, $this->addresses, $this->dns, null);
	}

	/**
	 * Tests the function to set values from the network connection configuration form
	 */
	public function testJsonDeserialize(): void {
		$arrayHash = ArrayHash::from([
			'method' => 'manual',
			'addresses' => [
				[
					'address' => '2001:470:5bb2::50c',
					'prefix' => 64,
					'gateway' => 'fe80::6f0:21ff:fe23:2900',
				],
			],
			'dns' => [['address' => 'fd50:ccd6:13ed::1']],
		], true);
		$addresses = [
			new IPv6Address(IPv6::factory('2001:470:5bb2::50c'), 64, IPv6::factory('fe80::6f0:21ff:fe23:2900')),
		];
		$dns = [IPv6::factory('fd50:ccd6:13ed::1')];
		$expected = new IPv6Connection($this->method, $addresses, $dns, null);
		$actual = IPv6Connection::jsonDeserialize($arrayHash);
		Assert::equal($expected, $actual);
	}

	/**
	 * Tests the function to create a new IPv6 connection entity from nmcli connection configuration
	 */
	public function testNmCliDeserialize(): void {
		$configuration = FileSystem::read(self::NM_DATA . '25ab1b06-2a86-40a9-950f-1c576ddcd35a.conf');
		Assert::equal($this->entity, IPv6Connection::nmCliDeserialize($configuration));
	}

	/**
	 * Tests the function to convert the IPv6 connection entity to an array for the form
	 */
	public function testJsonSerialize(): void {
		$expected = [
			'method' => 'manual',
			'addresses' => [
				[
					'address' => '2001:470:5bb2::2',
					'prefix' => 64,
					'gateway' => 'fe80::1',
				],
			],
			'dns' => [['address' => '2001:470:5bb2::1']],
		];
		Assert::same($expected, $this->entity->jsonSerialize());
	}

	/**
	 * Tests the function to convert IPv6 connection entity to nmcli configuration string
	 */
	public function testNmCliSerialize(): void {
		$expected = 'ipv6.method "manual" ipv6.addresses "2001:470:5bb2::2/64" ipv6.gateway "fe80::1" ipv6.dns "2001:470:5bb2::1" ';
		Assert::same($expected, $this->entity->nmCliSerialize());
	}

}

$test = new IPv6ConnectionTest();
$test->run();
