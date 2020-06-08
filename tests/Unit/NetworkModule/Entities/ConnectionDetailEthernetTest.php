<?php

/**
 * TEST: App\NetworkModule\Entities\ConnectionDetail
 * @covers App\NetworkModule\Entities\ConnectionDetail
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\NetworkModule\Entities;

use App\NetworkModule\Entities\ConnectionDetail;
use App\NetworkModule\Entities\IPv4Address;
use App\NetworkModule\Entities\IPv4Connection;
use App\NetworkModule\Entities\IPv6Address;
use App\NetworkModule\Entities\IPv6Connection;
use App\NetworkModule\Enums\ConnectionTypes;
use App\NetworkModule\Enums\IPv4Methods;
use App\NetworkModule\Enums\IPv6Methods;
use Darsyn\IP\Version\IPv4;
use Darsyn\IP\Version\IPv6;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for network connection entity
 */
class ConnectionDetailEthernetTest extends TestCase {

	/**
	 * @var string Network connection ID
	 */
	private $id = 'eth0';

	/**
	 * @var UuidInterface Network connection UUID
	 */
	private $uuid;

	/**
	 * @var ConnectionTypes Network connection type
	 */
	private $type;

	/**
	 * @var string Network interface name
	 */
	private $interfaceName = 'eth0';

	/**
	 * @var IPv4Connection IPv4 network connection entity
	 */
	private $ipv4;

	/**
	 * @var IPv6Connection IPv6 network connection entity
	 */
	private $ipv6;

	/**
	 * @var ConnectionDetail Network connection entity
	 */
	private $entity;

	/**
	 * Sets up the test environment
	 */
	public function __construct() {
		$this->uuid = Uuid::fromString('25ab1b06-2a86-40a9-950f-1c576ddcd35a');
		$this->type = ConnectionTypes::ETHERNET();
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->createIpv4Connection();
		$this->createIpv6Connection();
		$this->entity = new ConnectionDetail($this->id, $this->uuid, $this->type, $this->interfaceName, $this->ipv4, $this->ipv6);
	}

	/**
	 * Creates the IPv4 network connection entity
	 */
	private function createIpv4Connection(): void {
		$method = IPv4Methods::MANUAL();
		$addresses = [
			new IPv4Address(IPv4::factory('192.168.1.2'), 24),
		];
		$gateway = IPv4::factory('192.168.1.1');
		$dns = [
			IPv4::factory('192.168.1.1'),
		];
		$this->ipv4 = new IPv4Connection($method, $addresses, $gateway, $dns);
	}

	/**
	 * Creates the IPv6 network connection entity
	 */
	private function createIpv6Connection(): void {
		$method = IPv6Methods::MANUAL();
		$addresses = [
			new IPv6Address(IPv6::factory('2001:470:5bb2::2'), 64, IPv6::factory('fe80::1')),
		];
		$dns = [
			IPv6::factory('2001:470:5bb2::1'),
		];
		$this->ipv6 = new IPv6Connection($method, $addresses, $dns);
	}

	/**
	 * Tests the function to set the network connection configuration from the form
	 */
	public function testFromForm(): void {
		$json = FileSystem::read(__DIR__ . '/../../../data/networkManager/eth0FromForm.json');
		$arrayHash = Json::decode($json);
		$this->entity->fromForm($arrayHash);
		$ipv4Addresses = [IPv4Address::fromPrefix('10.0.0.2/16')];
		$ipv4Gateway = IPv4::factory('10.0.0.1');
		$ipv4Dns = [IPv4::factory('10.0.0.1'), IPv4::factory('1.1.1.1')];
		$ipv4 = new IPv4Connection(IPv4Methods::MANUAL(), $ipv4Addresses, $ipv4Gateway, $ipv4Dns);
		$ipv6Addresses = [IPv6Address::fromPrefix('2001:470:5bb2:2::2/64', 'fe80::1')];
		$ipv6Dns = [IPv6::factory('2001:470:5bb2:2::1')];
		$ipv6 = new IPv6Connection(IPv6Methods::MANUAL(), $ipv6Addresses, $ipv6Dns);
		$expected = new ConnectionDetail($this->id, $this->uuid, $this->type, $this->interfaceName, $ipv4, $ipv6);
		Assert::equal($expected, $this->entity);
	}

	/**
	 * Tests the function to create a detailed network connection entity from nmcli connection configuration
	 */
	public function testFromNmCli(): void {
		$nmCli = FileSystem::read(__DIR__ . '/../../../data/eth0.conf');
		Assert::equal($this->entity, ConnectionDetail::fromNmCli($nmCli));
	}

	/**
	 * Tests the function to get the network connection ID
	 */
	public function testGetId(): void {
		Assert::same($this->id, $this->entity->getId());
	}

	/**
	 * Tests the function to get the network connection UUID
	 */
	public function testGetUuid(): void {
		Assert::same($this->uuid, $this->entity->getUuid());
	}

	/**
	 * Tests the function to get the network connection type
	 */
	public function testGetType(): void {
		Assert::same($this->type, $this->entity->getType());
	}

	/**
	 * Tests the function to get the network interface name
	 */
	public function testGetInterfaceName(): void {
		Assert::same($this->interfaceName, $this->entity->getInterfaceName());
	}

	/**
	 * Tests the function to get the IPv4 network connection entity
	 */
	public function testGetIpv4(): void {
		Assert::same($this->ipv4, $this->entity->getIpv4());
	}

	/**
	 * Tests the function to get the IPv6 network connection entity
	 */
	public function testGetIpv6(): void {
		Assert::same($this->ipv6, $this->entity->getIpv6());
	}

	/**
	 * Tests the function to convert the network connection entity to an array for the form
	 */
	public function testToForm(): void {
		$json = FileSystem::read(__DIR__ . '/../../../data/networkManager/eth0ToForm.json');
		$expected = Json::decode($json, Json::FORCE_ARRAY);
		Assert::same($expected, $this->entity->toForm());
	}

	/**
	 * Tests the function to return JSON serialized data
	 */
	public function testJsonSerialize(): void {
		$json = FileSystem::read(__DIR__ . '/../../../data/networkManager/eth0ToForm.json');
		$expected = Json::decode($json, Json::FORCE_ARRAY);
		Assert::same($expected, $this->entity->jsonSerialize());
	}

}

$test = new ConnectionDetailEthernetTest();
$test->run();
