<?php
/**
 * TEST: App\NetworkModule\Models\ConnectionManager
 * @covers App\NetworkModule\Models\ConnectionManager
 * @phpVersion >= 7.1
 * @testCase
 */

declare(strict_types = 1);

namespace Tests\Unit\NetworkModule\Models;

use App\NetworkModule\Entities\Connection;
use App\NetworkModule\Entities\ConnectionDetail;
use App\NetworkModule\Entities\IPv4Address;
use App\NetworkModule\Entities\IPv4Connection;
use App\NetworkModule\Entities\IPv6Address;
use App\NetworkModule\Entities\IPv6Connection;
use App\NetworkModule\Enums\ConnectionTypes;
use App\NetworkModule\Enums\IPv4Methods;
use App\NetworkModule\Enums\IPv6Methods;
use App\NetworkModule\Models\ConnectionManager;
use Darsyn\IP\Version\IPv4;
use Darsyn\IP\Version\IPv6;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Ramsey\Uuid\Uuid;
use Tester\Assert;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for network connection manager
 */
class ConnectionManagerTest extends CommandTestCase {

	/**
	 * @var ConnectionManager Network connection manager
	 */
	private $manager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new ConnectionManager($this->commandManager);
	}

	/**
	 * Creates the detailed network connection entity
	 * @return ConnectionDetail Detailed network connection entity
	 */
	private function createDetailedConnection(): ConnectionDetail {
		$uuidStr = '25ab1b06-2a86-40a9-950f-1c576ddcd35a';
		$uuid = Uuid::fromString($uuidStr);
		$type = ConnectionTypes::ETHERNET();
		$ipv4 = $this->createIpv4Connection();
		$ipv6 = $this->createIpv6Connection();
		return new ConnectionDetail('eth0', $uuid, $type, 'eth0', $ipv4, $ipv6);
	}

	/**
	 * Creates the IPv4 network connection entity
	 * @return IPv4Connection IPv4 nmetwork connection entity
	 */
	private function createIpv4Connection(): IPv4Connection {
		$method = IPv4Methods::MANUAL();
		$addresses = [new IPv4Address(IPv4::factory('192.168.1.2'), 24)];
		$gateway = IPv4::factory('192.168.1.1');
		$dns = [IPv4::factory('192.168.1.1')];
		return new IPv4Connection($method, $addresses, $gateway, $dns);
	}

	/**
	 * Creates the IPv6 network connection entity
	 * @return IPv6Connection IPv6 network connection entity
	 */
	private function createIpv6Connection(): IPv6Connection {
		$method = IPv6Methods::MANUAL();
		$addresses = [new IPv6Address(IPv6::factory('2001:470:5bb2::2'), 64, IPv6::factory('fe80::1'))];
		$dns = [IPv6::factory('2001:470:5bb2::1')];
		return new IPv6Connection($method, $addresses, $dns);
	}

	/**
	 * Tests the function to delete network connection
	 */
	public function testDelete(): void {
		$uuid = '25ab1b06-2a86-40a9-950f-1c576ddcd35a';
		$command = 'nmcli -t connection delete ' . $uuid;
		$this->receiveCommand($command, true, '');
		Assert::noError(function () use ($uuid): void {
			$this->manager->delete(Uuid::fromString($uuid));
		});
	}

	/**
	 * Tests the function to get detailed network connection entity
	 */
	public function testGet(): void {
		$uuid = '25ab1b06-2a86-40a9-950f-1c576ddcd35a';
		$output = FileSystem::read(__DIR__ . '/../../../data/eth0.conf');
		$expected = $this->createDetailedConnection();
		$command = 'nmcli -t connection show ' . $uuid;
		$this->receiveCommand($command, true, $output);
		Assert::equal($expected, $this->manager->get(Uuid::fromString($uuid)));
	}

	/**
	 * Tests the function to list network connections
	 */
	public function testList(): void {
		$output = 'eth0:25ab1b06-2a86-40a9-950f-1c576ddcd35a:802-3-ethernet:eth0' . PHP_EOL
			. 'wlan0:dd1c59ea-6f5d-471c-8fe7-e066761b9764:802-11-wireless:' . PHP_EOL;
		$this->receiveCommand('nmcli -t connection show', true, $output);
		$expected = [
			new Connection('eth0', Uuid::fromString('25ab1b06-2a86-40a9-950f-1c576ddcd35a'), ConnectionTypes::ETHERNET(), 'eth0'),
			new Connection('wlan0', Uuid::fromString('dd1c59ea-6f5d-471c-8fe7-e066761b9764'), ConnectionTypes::WIFI(), ''),
		];
		Assert::equal($expected, $this->manager->list());
	}

	/**
	 * Tests the function to list network connections (empty list)
	 */
	public function testListEmpty(): void {
		$this->receiveCommand('nmcli -t connection show', true, '');
		$expected = [];
		Assert::equal($expected, $this->manager->list());
	}

	/**
	 * Tests the function to edit the network connection
	 */
	public function testEdit(): void {
		$connection = $this->createDetailedConnection();
		$json = FileSystem::read(__DIR__ . '/../../../data/networkManager/eth0FromForm.json');
		$jsonData = Json::decode($json);
		$command = 'nmcli -t connection modify 25ab1b06-2a86-40a9-950f-1c576ddcd35a ipv4.method "manual" ipv4.addresses "10.0.0.2/16" ipv4.gateway "10.0.0.1" ipv4.dns "10.0.0.1 1.1.1.1" ipv6.method "manual" ipv6.addresses "2001:470:5bb2:2::2/64" ipv6.gateway "fe80::1" ipv6.dns "2001:470:5bb2:2::1" ';
		$this->receiveCommand($command, true);
		Assert::noError(function () use ($connection, $jsonData): void {
			$this->manager->edit($connection, $jsonData);
		});
	}

	/**
	 * Tests the function to activate a connection on the interface
	 */
	public function testUp(): void {
		$connection = $this->createDetailedConnection();
		$command = 'nmcli -t connection up 25ab1b06-2a86-40a9-950f-1c576ddcd35a';
		$this->receiveCommand($command, true);
		Assert::noError(function () use ($connection): void {
			$this->manager->up($connection->getUuid());
		});
	}

}

$test = new ConnectionManagerTest();
$test->run();
