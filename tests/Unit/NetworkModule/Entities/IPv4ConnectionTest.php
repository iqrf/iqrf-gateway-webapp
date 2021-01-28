<?php

/**
 * TEST: App\NetworkModule\Entities\IPv4Connection
 * @covers App\NetworkModule\Entities\IPv4Connection
 * @phpVersion >= 7.2
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\NetworkModule\Entities;

use App\NetworkModule\Entities\IPv4Address;
use App\NetworkModule\Entities\IPv4Connection;
use App\NetworkModule\Enums\IPv4Methods;
use Darsyn\IP\Version\IPv4;
use Nette\Utils\ArrayHash;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for network connection entity
 */
final class IPv4ConnectionTest extends TestCase {

	/**
	 * NetworkManager data directory
	 */
	private const NM_DATA = __DIR__ . '/../../../data/networkManager/';

	/**
	 * @var IPv4Methods IPv4 connection method
	 */
	private $method;

	/**
	 * @var array<IPv4Address> IPv4 addresses
	 */
	private $addresses;

	/**
	 * @var IPv4 IPv4 gateway address
	 */
	private $gateway;

	/**
	 * @var array<IPv4> IPv4 addresses of DNS servers
	 */
	private $dns;

	/**
	 * @var IPv4Connection IPv4 connection entity
	 */
	private $entity;

	/**
	 * Sets up the test environment
	 */
	public function __construct() {
		$this->method = IPv4Methods::MANUAL();
		$this->addresses = [
			new IPv4Address(IPv4::factory('192.168.1.2'), 24),
		];
		$this->gateway = IPv4::factory('192.168.1.1');
		$this->dns = [IPv4::factory('192.168.1.1')];
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->entity = new IPv4Connection($this->method, $this->addresses, $this->gateway, $this->dns, null);
	}

	/**
	 * Tests the function to set values from the network connection form
	 */
	public function testJsonDeserialize(): void {
		$arrayHash = ArrayHash::from([
			'method' => 'manual',
			'addresses' => [
				[
					'address' => '10.0.0.2',
					'mask' => '255.255.0.0',
				],
			],
			'gateway' => '10.0.0.1',
			'dns' => [
				['address' => '10.0.0.1'],
				['address' => '1.1.1.1'],
			],
		], true);
		$addresses = [
			new IPv4Address(IPv4::factory('10.0.0.2'), 16),
		];
		$gateway = IPv4::factory('10.0.0.1');
		$dns = [IPv4::factory('10.0.0.1'), IPv4::factory('1.1.1.1')];
		$expected = new IPv4Connection($this->method, $addresses, $gateway, $dns, null);
		$actual = IPv4Connection::jsonDeserialize($arrayHash);
		Assert::equal($expected, $actual);
	}

	/**
	 * Tests the function to create a new IPv4 connection entity from nmcli connection configuration
	 */
	public function testNmCliDeserialize(): void {
		$configuration = FileSystem::read(self::NM_DATA . '25ab1b06-2a86-40a9-950f-1c576ddcd35a.conf');
		Assert::equal($this->entity, IPv4Connection::nmCliDeserialize($configuration));
	}

	/**
	 * Tests the function to convert the IPv4 connection entity to an array for the form
	 */
	public function testJsonSerialize(): void {
		$expected = [
			'method' => 'manual',
			'addresses' => [
				[
					'address' => '192.168.1.2',
					'prefix' => 24,
					'mask' => '255.255.255.0',
				],
			],
			'gateway' => '192.168.1.1',
			'dns' => [['address' => '192.168.1.1']],
		];
		Assert::same($expected, $this->entity->jsonSerialize());
	}

	/**
	 * Tests the function to convert IPv4 connection entity to nmcli configuration string
	 */
	public function testNmCliSerialize(): void {
		$expected = 'ipv4.method "manual" ipv4.addresses "192.168.1.2/24" ipv4.gateway "192.168.1.1" ipv4.dns "192.168.1.1" ';
		Assert::same($expected, $this->entity->nmCliSerialize());
	}

}

$test = new IPv4ConnectionTest();
$test->run();
