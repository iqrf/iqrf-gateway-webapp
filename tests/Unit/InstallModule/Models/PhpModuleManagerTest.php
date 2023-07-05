<?php

/**
 * TEST: App\InstallModule\Models\SudoManager
 * @covers App\InstallModule\Models\SudoManager
 * @phpVersion >= 7.4
 * @testCase
 */
/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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

namespace Tests\Unit\InstallModule\Models;

use App\InstallModule\Models\PhpModuleManager;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for PHP modules manager
 */
final class PhpModuleManagerTest extends TestCase {

	/**
	 * Tests the function to check installed and loaded PHP modules
	 */
	public function testCheckModules(): void {
		Assert::noError(static function (): void {
			PhpModuleManager::checkModules();
		});
	}

}

$test = new PhpModuleManagerTest();
$test->run();
