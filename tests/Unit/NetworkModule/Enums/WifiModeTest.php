<?php

/**
 * TEST: App\NetworkModule\Enums\WifiMode
 * @covers App\NetworkModule\Enums\WifiMode
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

namespace Tests\Unit\NetworkModule\Enums;

use App\NetworkModule\Enums\WifiMode;
use Tester\Assert;
use Tester\TestCase;
use ValueError;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for WiFi mode enum
 */
final class WifiModeTest extends TestCase {

	/**
	 * Returns list of test data for testFromNetworkList() method
	 * @return array<array<WifiMode|string>> List of test data for testFromNetworkList() method
	 */
	public function getFromNetworkListData(): array {
		return [
			[
				WifiMode::ADHOC,
				'Ad-Hoc',
			],
			[
				WifiMode::INFRA,
				'Infra',
			],
			[
				WifiMode::MESH,
				'Mesh',
			],
		];
	}

	/**
	 * Tests the function for creating WiFi mode enum
	 * @dataProvider getFromNetworkListData
	 * @param WifiMode $expected Expected WiFi mode enum
	 * @param string $mode WiFi network mode scalar
	 */
	public function testFromNetworkList(WifiMode $expected, string $mode): void {
		Assert::equal($expected, WifiMode::fromNetworkList($mode));
	}

	/**
	 * Tests the function for creating WiFi mode enum - Unknown
	 */
	public function testFromNetworkListUnknown(): void {
		Assert::throws(static function (): void {
			WifiMode::fromNetworkList('Unknown');
		}, ValueError::class, 'There is no value for enum \'' . WifiMode::class . '\' and scalar value \'Unknown\'.');
	}

}

$test = new WifiModeTest();
$test->run();
