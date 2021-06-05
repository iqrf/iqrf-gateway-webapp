<?php

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
/**
 * TEST: App\NetworkModule\Enums\WifiMode
 * @covers App\NetworkModule\Enums\WifiMode
 * @phpVersion >= 7.2
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\NetworkModule\Enums;

use App\NetworkModule\Enums\WifiMode;
use Grifart\Enum\MissingValueDeclarationException;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for WiFi mode enum
 */
final class WifiModeTest extends TestCase {

	/**
	 * Tests the function for creating WiFi mode enum - Ad-Hoc
	 */
	public function testFromNetworkListAdHoc(): void {
		$expected = WifiMode::ADHOC();
		Assert::equal($expected, WifiMode::fromNetworkList('Ad-Hoc'));
	}

	/**
	 * Tests the function for creating WiFi mode enum - Infrastructure
	 */
	public function testFromNetworkListInfrastructure(): void {
		$expected = WifiMode::INFRA();
		Assert::equal($expected, WifiMode::fromNetworkList('Infra'));
	}

	/**
	 * Tests the function for creating WiFi mode enum - Mesh
	 */
	public function testFromNetworkListMesh(): void {
		$expected = WifiMode::MESH();
		Assert::equal($expected, WifiMode::fromNetworkList('Mesh'));
	}

	/**
	 * Tests the function for creating WiFi mode enum - Unknown
	 */
	public function testFromNetworkListUnknown(): void {
		Assert::throws(function (): void {
			WifiMode::fromNetworkList('Unknown');
		}, MissingValueDeclarationException::class, 'There is no value for enum \'' . WifiMode::class . '\' and scalar value \'Unknown\'.');
	}

}

$test = new WifiModeTest();
$test->run();
