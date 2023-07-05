<?php

/**
 * TEST: App\NetworkModule\Entities\IPv4Connection
 * @covers App\NetworkModule\Entities\IPv4Connection
 * @phpVersion >= 7.4
 * @testCase
 */
/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
declare(strict_types = 1);

namespace Tests\Unit\NetworkModule\Entities;

use App\NetworkModule\Entities\IPv4Address;
use App\NetworkModule\Entities\IPv4Connection;
use App\NetworkModule\Enums\IPv4Methods;
use App\NetworkModule\Utils\NmCliConnection;
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
	 * @var string NetworkManager data directory
	 */
	private const NM_DATA = TESTER_DIR . '/data/networkManager/';

	/**
	 * @var IPv4Methods IPv4 connection method
	 */
	private const METHOD = IPv4Methods::MANUAL;

	/**
	 * @var array<IPv4Address> IPv4 addresses
	 */
	private readonly array $addresses;

	/**
	 * @var IPv4 IPv4 gateway address
	 */
	private readonly IPv4 $gateway;

	/**
	 * @var array<IPv4> IPv4 addresses of DNS servers
	 */
	private readonly array $dns;

	/**
	 * @var IPv4Connection IPv4 connection entity
	 */
	private IPv4Connection $entity;

	/**
	 * Sets up the test environment
	 */
	public function __construct() {
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
		$this->entity = new IPv4Connection(self::METHOD, $this->addresses, $this->gateway, $this->dns, null);
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
					'prefix' => 16,
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
		$expected = new IPv4Connection(self::METHOD, $addresses, $gateway, $dns, null);
		$actual = IPv4Connection::jsonDeserialize($arrayHash);
		Assert::equal($expected, $actual);
	}

	/**
	 * Tests the function to create a new IPv4 connection entity from nmcli connection configuration
	 */
	public function testNmCliDeserialize(): void {
		$configuration = FileSystem::read(self::NM_DATA . '25ab1b06-2a86-40a9-950f-1c576ddcd35a.conf');
		$configuration = NmCliConnection::decode($configuration);
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
