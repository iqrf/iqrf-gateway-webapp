<?php

/**
 * TEST: App\NetworkModule\Enums\WifiSecurity
 * @covers App\NetworkModule\Enums\WifiSecurity
 * @phpVersion >= 7.4
 * @testCase
 */
/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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

use App\NetworkModule\Enums\WifiSecurity;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for WiFi security enum
 */
final class WifiSecurityTest extends TestCase {

	/**
	 * Returns list of test data for testFromNmCli() method
	 * @return array<array<WifiSecurity|string>> List of test data for testFromNmCli() method
	 */
	public function getFromNmCliData(): array {
		return [
			[WifiSecurity::OPEN, ''],
			[WifiSecurity::OWE, 'OWE'],
			[WifiSecurity::WEP, 'WEP'],
			[WifiSecurity::WPA_ENTERPRISE, 'WPA 802.1X'],
			[WifiSecurity::WPA_PERSONAL, 'WPA'],
			[WifiSecurity::WPA2_ENTERPRISE, 'WPA2 802.1X'],
			[WifiSecurity::WPA2_PERSONAL, 'WPA2'],
			[WifiSecurity::WPA3_ENTERPRISE, 'WPA3 802.1X'],
			[WifiSecurity::WPA3_PERSONAL, 'WPA3'],
		];
	}

	/**
	 * Tests the function for creating WiFi security enum
	 * @dataProvider getFromNmCliData
	 */
	public function testFromNmCli(WifiSecurity $expected, string $nmCli): void {
		Assert::equal($expected, WifiSecurity::fromNmCli($nmCli));
	}

}

$test = new WifiSecurityTest();
$test->run();
