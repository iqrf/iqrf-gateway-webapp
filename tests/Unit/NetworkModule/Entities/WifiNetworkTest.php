<?php

/**
 * TEST: App\NetworkModule\Entities\WifiNetwork
 * @covers App\NetworkModule\Entities\WifiNetwork
 * @phpVersion >= 7.3
 * @testCase
 */
/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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

use App\NetworkModule\Entities\WifiNetwork;
use App\NetworkModule\Enums\WifiMode;
use App\NetworkModule\Enums\WifiSecurity;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for network connection entity
 */
final class WifiNetworkTest extends TestCase {

	/**
	 * Is in use?
	 */
	private const IN_USE = true;

	/**
	 * BSSID (MAC address)
	 */
	private const BSSID = '1A:E8:29:E5:CB:9A';

	/**
	 * SSID
	 */
	private const SSID = 'WIFI MAGDA';

	/**
	 * @var WifiMode Mode
	 */
	private $mode;

	/**
	 * Channel
	 */
	private const CHANNEL = 56;

	/**
	 * Speed rate
	 */
	private const RATE = '405 Mbit/s';

	/**
	 * Signal strength
	 */
	private const SIGNAL = 70;

	/**
	 * @var WifiSecurity Security
	 */
	private $security;

	/**
	 * @var WifiNetwork WiFI network entity
	 */
	private $entity;

	/**
	 * Sets up the test environment
	 */
	public function __construct() {
		$this->mode = WifiMode::INFRA();
		$this->security = WifiSecurity::WPA2_PERSONAL();
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->entity = new WifiNetwork(self::IN_USE, self::BSSID, self::SSID, $this->mode, self::CHANNEL, self::RATE, self::SIGNAL, $this->security);
	}

	/**
	 * Tests the function to create a new WiFi network entity from nmcli
	 */
	public function testFromNmCli(): void {
		$actual = WifiNetwork::nmCliDeserialize('*:1A\:E8\:29\:E5\:CB\:9A:WIFI MAGDA:Infra:56:405 Mbit/s:70:▂▄▆_:WPA2');
		Assert::equal($this->entity, $actual);
	}

	/**
	 * Tests the function to check if the network is used
	 */
	public function testIsUsed(): void {
		Assert::true($this->entity->isUsed());
	}

	/**
	 * Tests the function to get the network's BSSID
	 */
	public function testGetBssid(): void {
		Assert::same(self::BSSID, $this->entity->getBssid());
	}

	/**
	 * Tests the function to get the network's SSID
	 */
	public function testGetSsid(): void {
		Assert::same(self::SSID, $this->entity->getSsid());
	}

	/**
	 * Tests the function to get the network's mode
	 */
	public function testGetMode(): void {
		Assert::equal($this->mode, $this->entity->getMode());
	}

	/**
	 * Tests the function to get the network's channel
	 */
	public function testGetChannel(): void {
		Assert::same(self::CHANNEL, $this->entity->getChannel());
	}

	/**
	 * Tests the function to get the network's speed rate
	 */
	public function testGetRate(): void {
		Assert::same(self::RATE, $this->entity->getRate());
	}

	/**
	 * Tests the function to get the network's signal strength
	 */
	public function testGetSignal(): void {
		Assert::same(self::SIGNAL, $this->entity->getSignal());
	}

	/**
	 * Tests the function to get JSON serialized data
	 */
	public function testJsonSerialize(): void {
		$expected = [
			'inUse' => true,
			'bssid' => '1A:E8:29:E5:CB:9A',
			'ssid' => 'WIFI MAGDA',
			'mode' => 'infrastructure',
			'channel' => 56,
			'rate' => '405 Mbit/s',
			'signal' => 70,
			'security' => 'WPA2-Personal',

		];
		Assert::same($expected, $this->entity->jsonSerialize());
	}

}

$test = new WifiNetworkTest();
$test->run();
