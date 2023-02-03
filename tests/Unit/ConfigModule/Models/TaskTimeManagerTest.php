<?php

/**
 * TEST: App\ConfigModule\Models\TaskTimeManager
 * @covers App\ConfigModule\Models\TaskTimeManager
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

namespace Tests\Unit\ConfigModule\Models;

use App\ConfigModule\Models\TaskTimeManager;
use stdClass;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for scheduler's task time specification manager
 */
final class TaskTimeManagerTest extends TestCase {

	/**
	 * @var TaskTimeManager Scheduler's task time specification manager
	 */
	private TaskTimeManager $manager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->manager = new TaskTimeManager();
	}

	/**
	 * Returns list of test data for getCronToArray() method
	 * @return array<array<stdClass>> List of test data for getCronToArray() method
	 */
	public function getCronToArrayData(): array {
		return [
			[
				(object) [
					'timeSpec' => (object) [
						'cronTime' => '@daily',
					],
				],
				(object) [
					'timeSpec' => (object) [
						'cronTime' => ['@daily', '', '', '', '', '', ''],
					],
				],
			],
			[
				(object) [
					'timeSpec' => (object) [
						'cronTime' => '@invalid',
					],
				],
				(object) [
					'timeSpec' => (object) [
						'cronTime' => ['', '', '', '', '', '', ''],
					],
				],
			],
			[
				(object) [
					'timeSpec' => (object) [
						'cronTime' => '0 0 * * *',
					],
				],
				(object) [
					'timeSpec' => (object) [
						'cronTime' => ['0', '0', '0', '*', '*', '*', '*'],
					],
				],
			],
			[
				(object) [
					'timeSpec' => (object) [
						'cronTime' => '0 0 0 * * *',
					],
				],
				(object) [
					'timeSpec' => (object) [
						'cronTime' => ['0', '0', '0', '*', '*', '*', '*'],
					],
				],
			],
			[
				(object) [
					'timeSpec' => (object) [
						'cronTime' => '0 0 * * * 2020',
					],
				],
				(object) [
					'timeSpec' => (object) [
						'cronTime' => ['0', '0', '0', '*', '*', '*', '2020'],
					],
				],
			],
			[
				(object) [
					'timeSpec' => (object) [
						'cronTime' => '0 0 12 ? * 1,2,3,4,5 *',
					],
				],
				(object) [
					'timeSpec' => (object) [
						'cronTime' => ['0', '0', '12', '*', '*', '1,2,3,4,5', '*'],
					],
				],
			],
			[
				(object) [
					'timeSpec' => (object) [
						'cronTime' => 'INVALID *',
					],
				],
				(object) [
					'timeSpec' => (object) [
						'cronTime' => ['', '', '', '', '', '', ''],
					],
				],
			],
		];
	}

	/**
	 * Tests the function to convert CRON alias to an array
	 * @dataProvider getCronToArrayData
	 * @param stdClass $config Configuration to fix
	 * @param stdClass $expected Expected configuration
	 */
	public function testCronToArray(stdClass $config, stdClass $expected): void {
		$this->manager->cronToArray($config);
		Assert::equal($expected, $config);
	}

	/**
	 * Tests the function to convert CRON in an array to a string
	 */
	public function testCronToString(): void {
		$config = (object) [
			'timeSpec' => (object) [
				'cronTime' => ['0', '0', '0', '*', '*', '*', '*'],
			],
		];
		$expected = (object) [
			'timeSpec' => (object) [
				'cronTime' => '0 0 0 * * * *',
			],
		];
		$this->manager->cronToString($config);
		Assert::equal($expected, $config);
	}

}

$test = new TaskTimeManagerTest();
$test->run();
