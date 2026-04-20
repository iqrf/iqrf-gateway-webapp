<?php

/**
 * TEST: App\Models\Database\Enums\UserState
 * @covers App\Models\Database\Enums\UserState
 * @phpVersion >= 8.2
 * @testCase
 */
/**
 * Copyright 2017-2026 IQRF Tech s.r.o.
 * Copyright 2019-2026 MICRORISC s.r.o.
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

namespace Tests\Unit\Models\Database\Enums;

use App\Models\Database\Enums\UserState;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for user invitation database entity
 */
final class UserStateTest extends TestCase {

	/**
	 * Returns test data for user blocking and unblocking
	 * @return array<int, array{0: UserState, 1: UserState}> Data provider for blocked user states
	 */
	public function getBlockDataProvider(): array {
		return [
			[UserState::Unverified, UserState::BlockedUnverified],
			[UserState::Verified, UserState::BlockedVerified],
			[UserState::Invited, UserState::BlockedInvited],
		];
	}

	/**
	 * Returns test data for string serialization of user state
	 * @return array<int, array{0: UserState, 1: string}> Data provider for user state to string conversion
	 */
	public function getToStringDataProvider(): array {
		return [
			[UserState::Unverified, 'unverified'],
			[UserState::Verified, 'verified'],
			[UserState::BlockedUnverified, 'blocked'],
			[UserState::BlockedVerified, 'blocked'],
			[UserState::Invited, 'invited'],
			[UserState::BlockedInvited, 'blocked'],
		];
	}

	/**
	 * Tests the function to get the new user blocked state
	 * @dataProvider getBlockDataProvider
	 * @param UserState $state Current user state
	 * @param UserState $expected Expected user state
	 */
	public function testBlock(UserState $state, UserState $expected): void {
		Assert::equal($expected, $state->block());
	}

	/**
	 * Tests the function to get the new user blocked state
	 * @dataProvider getBlockDataProvider
	 * @param UserState $expected Expected user state
	 * @param UserState $state Current user state
	 */
	public function testUnlock(UserState $expected, UserState $state): void {
		Assert::equal($expected, $state->unblock());
	}

	/**
	 * Tests the function to get string representation of user state
	 * @dataProvider getToStringDataProvider
	 * @param UserState $state User state
	 * @param string $expected Expected user state string
	 */
	public function testToString(UserState $state, string $expected): void {
		Assert::equal($expected, $state->toString());
	}

	/**
	 * Tests the function to get JSON string representation of user state
	 * @dataProvider getToStringDataProvider
	 * @param UserState $state User state
	 * @param string $expected Expected user state string
	 */
	public function testJsonSerialize(UserState $state, string $expected): void {
		Assert::equal($expected, $state->jsonSerialize());
	}

}

$test = new UserStateTest();
$test->run();
