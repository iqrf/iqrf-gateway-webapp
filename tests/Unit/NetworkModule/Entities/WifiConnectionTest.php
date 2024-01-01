<?php

/**
 * TEST: App\NetworkModule\Entities\WifiConnection
 * @covers App\NetworkModule\Entities\WifiConnection
 * @phpVersion >= 7.4
 * @testCase
 */
/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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

use App\NetworkModule\Entities\WifiConnection;
use App\NetworkModule\Entities\WifiConnectionSecurity;
use App\NetworkModule\Entities\WifiSecurity\Leap;
use App\NetworkModule\Entities\WifiSecurity\Wep;
use App\NetworkModule\Enums\WepKeyType;
use App\NetworkModule\Enums\WifiMode;
use App\NetworkModule\Enums\WifiSecurityType;
use App\NetworkModule\Utils\NmCliConnection;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for WiFi connection entity
 */
final class WifiConnectionTest extends TestCase {

	/**
	 * NetworkManager data directory
	 */
	private const NM_DATA = TESTER_DIR . '/data/networkManager/';

	/**
	 * @var WifiConnection WiFi connection entity
	 */
	private WifiConnection $entity;

	/**
	 * Tests the function to create a new IPv6 connection entity from nmcli connection configuration
	 */
	public function testNmCliDeserialize(): void {
		$configuration = FileSystem::read(self::NM_DATA . '5c7010a8-88f6-48e6-8ab2-5ad713217831.conf');
		$configuration = NmCliConnection::decode($configuration);
		Assert::equal($this->entity, WifiConnection::nmCliDeserialize($configuration));
	}

	/**
	 * Tests the function to return JSON serialized data
	 */
	public function testJsonSerialize(): void {
		$expected = [
			'ssid' => 'WIFI MAGDA',
			'mode' => 'infrastructure',
			'bssids' => [
				'04:4F:4C:AB:DD:6A',
				'04:F0:21:23:29:00',
				'04:F0:21:24:1E:53',
				'18:E8:29:E4:CB:9A',
				'1A:E8:29:E5:CB:9A',
			],
			'security' => [
				'type' => 'wpa-psk',
				'psk' => 'password',
				'leap' => [
					'username' => '',
					'password' => '',
				],
				'wep' => [
					'type' => 'unknown',
					'index' => 0,
					'keys' => ['', '', '', ''],
				],
			],
		];
		Assert::same($expected, $this->entity->jsonSerialize());
	}

	/**
	 * Tests the function to convert WiFi connection entity to nmcli configuration string
	 */
	public function testNmCliSerialize(): void {
		$expected = '802-11-wireless.ssid "WIFI MAGDA" 802-11-wireless.mode "infrastructure" 802-11-wireless-security.key-mgmt "wpa-psk" 802-11-wireless-security.psk "password" 802-11-wireless-security.leap-password "" 802-11-wireless-security.leap-username "" 802-11-wireless-security.wep-key-type "unknown" 802-11-wireless-security.wep-tx-keyidx "0" 802-11-wireless-security.wep-key0 "" 802-11-wireless-security.wep-key1 "" 802-11-wireless-security.wep-key2 "" 802-11-wireless-security.wep-key3 "" ';
		Assert::same($expected, $this->entity->nmCliSerialize());
	}

	/**
	 * Sets up the testing environment
	 */
	protected function setUp(): void {
		$ssid = 'WIFI MAGDA';
		$mode = WifiMode::INFRA;
		$bssids = ['04:4F:4C:AB:DD:6A', '04:F0:21:23:29:00', '04:F0:21:24:1E:53', '18:E8:29:E4:CB:9A', '1A:E8:29:E5:CB:9A'];
		$leap = new Leap('', '');
		$wep = new Wep(WepKeyType::UNKNOWN, 0, ['', '', '', '']);
		$security = new WifiConnectionSecurity(WifiSecurityType::WPA_PSK, 'password', $leap, $wep, null);
		$this->entity = new WifiConnection($ssid, $mode, $bssids, $security);
	}

}

$test = new WifiConnectionTest();
$test->run();
