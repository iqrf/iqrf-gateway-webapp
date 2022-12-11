<?php

/**
 * TEST: App\NetworkModule\Enums\WifiSecurityType
 * @covers App\NetworkModule\Enums\WifiSecurityType
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

namespace Tests\Unit\NetworkModule\Enums;

use App\NetworkModule\Enums\WifiSecurityType;
use App\NetworkModule\Utils\NmCliConnection;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for WiFi security type enum
 */
final class WifiSecurityTypeTest extends TestCase {

	/**
	 * Returns list of test data for testNmCliDeserialize() method
	 * @return array<array<WifiSecurityType|string>> List of test data for testNmCliDeserialize() method
	 */
	public function getNmCliDeserializeData(): array {
		return [
			[
				WifiSecurityType::OPEN(),
				'',
			],
			[
				WifiSecurityType::LEAP(),
				'802-11-wireless-security.key-mgmt:ieee8021x' . PHP_EOL . '802-11-wireless-security.auth-alg:leap',
			],
			[
				WifiSecurityType::WEP(),
				'802-11-wireless-security.key-mgmt:none' . PHP_EOL . '802-11-wireless-security.auth-alg:open',
			],
			[
				WifiSecurityType::WPA_EAP(),
				'802-11-wireless-security.key-mgmt:wpa-eap',
			],
			[
				WifiSecurityType::WPA_PSK(),
				'802-11-wireless-security.key-mgmt:wpa-psk',
			],
		];
	}

	/**
	 * Tests the function to deserialize WiFi security type from nmcli configuration string
	 * @dataProvider getNmCliDeserializeData
	 * @param WifiSecurityType $expected Expected WiFi security entity
	 * @param string $nmCli nmcli configuration string
	 */
	public function testNmCliDeserialize(WifiSecurityType $expected, string $nmCli): void {
		$nmCli = NmCliConnection::decode($nmCli);
		$actual = WifiSecurityType::nmCliDeserialize($nmCli);
		Assert::same($expected, $actual);
	}

	/**
	 * Returns list of test data for testNmCliSerialize() method
	 * @return array<array<string|WifiSecurityType>> List of test data for testNmCliSerialize() method
	 */
	public function getNmCliSerializeData(): array {
		return [
			[
				'',
				WifiSecurityType::OPEN(),
			],
			[
				'802-11-wireless-security.key-mgmt "ieee8021x" 802-11-wireless-security.auth-alg "leap" ',
				WifiSecurityType::LEAP(),
			],
			[
				'802-11-wireless-security.key-mgmt "none" 802-11-wireless-security.auth-alg "open" ',
				WifiSecurityType::WEP(),
			],
			[
				'802-11-wireless-security.key-mgmt "wpa-eap" ',
				WifiSecurityType::WPA_EAP(),
			],
			[
				'802-11-wireless-security.key-mgmt "wpa-psk" ',
				WifiSecurityType::WPA_PSK(),
			],
		];
	}

	/**
	 * Tests the function to serialize WiFi security type into nmcli configuration string
	 * @dataProvider getNmCliSerializeData
	 * @param string $expected Expected nmcli configuration string,
	 * @param WifiSecurityType $type WiFi security type
	 */
	public function testNmCliSerialize(string $expected, WifiSecurityType $type): void {
		Assert::same($expected, $type->nmCliSerialize());
	}

}

$test = new WifiSecurityTypeTest();
$test->run();
