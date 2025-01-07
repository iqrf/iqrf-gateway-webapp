<?php

/**
 * TEST: App\NetworkModule\Enums\InterfaceStates
 * @covers App\NetworkModule\Enums\InterfaceStates
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

use App\NetworkModule\Enums\InterfaceStates;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for network interface state enum
 */
final class InterfaceStatesTest extends TestCase {

	/**
	 * Returns network interface states
	 * @return array<array<InterfaceStates|string>> List of test data for testFromNmCli() method
	 */
	public function getStates(): array {
		return [
			['100 (connected (externally))', InterfaceStates::CONNECTED],
			['100 (connected)', InterfaceStates::CONNECTED],
			['60 (connecting (need authentication))', InterfaceStates::NEED_AUTH],
		];
	}

	/**
	 * Tests the function to parse network interface state from nmcli output
	 * @param string $nmCli NetworkManager CLI output
	 * @param InterfaceStates $expected Expected state
	 * @dataProvider getStates
	 */
	public function testFromNmCli(string $nmCli, InterfaceStates $expected): void {
		Assert::equal($expected, InterfaceStates::fromNmCli($nmCli));
	}

}

$test = new InterfaceStatesTest();
$test->run();
