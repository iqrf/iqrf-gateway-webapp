<?php

/**
 * TEST: App\IqrfNetModule\Models\EnumerationManager
 * @covers App\IqrfNetModule\Models\EnumerationManager
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

namespace Tests\Unit\IqrfNetModule\Models;

use App\IqrfNetModule\Models\EnumerationManager;
use Tests\Toolkit\TestCases\WebSocketTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Test for IQMESH Enumeration manager
 */
final class EnumerationManagerTest extends WebSocketTestCase {

	/**
	 * Network device address
	 */
	private const ADDRESS = 1;

	/**
	 * @var EnumerationManager IQMESH Enumeration manager
	 */
	private EnumerationManager $manager;

	/**
	 * Tests the function to run IQMESH Enumeration process
	 */
	public function testRun(): void {
		$request = [
			'mType' => 'iqmeshNetwork_EnumerateDevice',
			'data' => [
				'repeat' => 2,
				'req' => [
					'deviceAddr' => self::ADDRESS,
					'morePeripheralsInfo' => true,
				],
				'returnVerbose' => true,
			],
		];
		$this->assertRequest($request, function (): void {
			$this->manager->device(self::ADDRESS);
		});
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new EnumerationManager($this->request, $this->wsClient);
	}

}

$test = new EnumerationManagerTest();
$test->run();
