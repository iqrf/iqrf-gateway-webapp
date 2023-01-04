<?php

/**
 * TEST: App\NetworkModule\Entities\WifiNetwork
 * @covers App\NetworkModule\Entities\WifiNetwork
 * @phpVersion >= 7.4
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
	 * @var bool Is in use?
	 */
	private const IN_USE = true;

	/**
	 * @var string BSSID (MAC address)
	 */
	private const BSSID = '1A:E8:29:E5:CB:9A';

	/**
	 * @var string SSID
	 */
	private const SSID = 'WIFI MAGDA';

	/**
	 * @var WifiMode Mode
	 */
	private WifiMode $mode;

	/**
	 * @var int Channel
	 */
	private const CHANNEL = 56;

	/**
	 * @var string Speed rate
	 */
	private const RATE = '405 Mbit/s';

	/**
	 * @var int Signal strength
	 */
	private const SIGNAL = 70;

	/**
	 * @var WifiSecurity Security
	 */
	private WifiSecurity $security;

	/**
	 * @var WifiNetwork Wi-Fi network entity
	 */
	private WifiNetwork $entity;

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
		$actual = WifiNetwork::nmCliDeserialize('*:1A\:E8\:29\:E5\:CB\:9A:WIFI MAGDA:Infra:56:405 Mbit/s:70:WPA2');
		Assert::equal($this->entity, $actual);
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
