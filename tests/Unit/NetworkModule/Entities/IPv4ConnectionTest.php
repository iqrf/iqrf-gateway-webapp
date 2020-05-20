<?php

/**
 * TEST: App\NetworkModule\Entities\IPv4Connection
 * @covers App\NetworkModule\Entities\IPv4Connection
 * @phpVersion >= 7.1
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
class IPv4ConnectionTest extends TestCase {

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
		$this->entity = new IPv4Connection($this->method, $this->addresses, $this->gateway, $this->dns);
	}

	/**
	 * Tests the function to set values from the network connection form
	 */
	public function testFromForm(): void {
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
		$expected = new IPv4Connection($this->method, $addresses, $gateway, $dns);
		$this->entity->fromForm($arrayHash);
		Assert::equal($expected, $this->entity);
	}

	/**
	 * Tests the function to create a new IPv4 connection entity from nmcli connection configuration
	 */
	public function testFromNmCli(): void {
		$configuration = FileSystem::read(__DIR__ . '/../../../data/eth0.conf');
		Assert::equal($this->entity, IPv4Connection::fromNmCli($configuration));
	}

	/**
	 * Tests the function to get IPv4 connection method
	 */
	public function testGetMethod(): void {
		Assert::same($this->method, $this->entity->getMethod());
	}

	/**
	 * Tests the function to get IPv4 addresses
	 */
	public function testGetAddresses(): void {
		Assert::same($this->addresses, $this->entity->getAddresses());
	}

	/**
	 * Tests the function to get IPv4 gateway address
	 */
	public function testGetGateway(): void {
		Assert::same($this->gateway, $this->entity->getGateway());
	}

	/**
	 * Tests the function to get IPv4 addresses of DNS servers
	 */
	public function testGetDns(): void {
		Assert::same($this->dns, $this->entity->getDns());
	}

	/**
	 * Tests the function to convert the IPv4 connection entity to an array for the form
	 */
	public function testToForm(): void {
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
		Assert::same($expected, $this->entity->toForm());
	}

	/**
	 * Tests the function to convert IPv4 connection entity to nmcli configuration string
	 */
	public function testToNmCli(): void {
		$expected = 'ipv4.method "manual" ipv4.addresses "192.168.1.2/24" ipv4.gateway "192.168.1.1" ipv4.dns "192.168.1.1" ';
		Assert::same($expected, $this->entity->toNmCli());
	}

}

$test = new IPv4ConnectionTest();
$test->run();
