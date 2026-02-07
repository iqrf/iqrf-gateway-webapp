<?php

/**
 * TEST: App\GatewayModule\Models\Backup\PosixHelper
 * @covers App\GatewayModule\Models\Backup\PosixHelper
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

namespace Tests\Unit\GatewayModule\Models\Backup;

use App\GatewayModule\Models\Backup\PosixHelper;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for POSIX account helper
 */
final class PosixHelperTest extends TestCase {

	/**
	 * Tests the function to get username and group name in format "username:groupname" to be used for chown
	 */
	public function testGetControllerEmpty(): void {
		$userId = posix_geteuid();
		$userInfo = posix_getpwuid($userId);
		if ($userInfo === false) {
			Assert::fail('Failed to get user info for current user ID');
		}
		$groupInfo = posix_getgrgid($userInfo['gid']);
		if ($groupInfo === false) {
			Assert::fail('Failed to get group info for current user ID');
		}
		$expected = '\'' . $userInfo['name'] . ':' . $groupInfo['name'] . '\'';
		Assert::same($expected, PosixHelper::getChownOwner());
	}

}

$test = new PosixHelperTest();
$test->run();
