<?php

/**
 * TEST: App\NetworkModule\Models\ConnectivityManager
 * @covers App\NetworkModule\Models\ConnectivityManager
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

namespace Tests\Unit\NetworkModule\Models;

use App\NetworkModule\Enums\ConnectivityState;
use App\NetworkModule\Exceptions\NetworkManagerException;
use App\NetworkModule\Models\ConnectivityManager;
use Tester\Assert;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for network connectivity manager
 */
final class ConnectivityManagerTest extends CommandTestCase {

	/**
	 * @var ConnectivityManager Network connectivity manager
	 */
	private ConnectivityManager $manager;

	/**
	 * @var string Connectivity check command
	 */
	private const CHECK_CMD = 'nmcli -t networking connectivity check';

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new ConnectivityManager($this->commandManager);
	}

	/**
	 * Tests the function to check network connectivity (failure)
	 */
	public function testCheckFailure(): void {
		$this->receiveCommand(self::CHECK_CMD, true, '', '', 10);
		Assert::throws(function (): void {
			$this->manager->check();
		}, NetworkManagerException::class);
	}

	/**
	 * Tests the function to check network connectivity (success)
	 */
	public function testCheckSuccess(): void {
		$this->receiveCommand(self::CHECK_CMD, true, 'full');
		Assert::equal(ConnectivityState::FULL(), $this->manager->check());
	}

}

$test = new ConnectivityManagerTest();
$test->run();
