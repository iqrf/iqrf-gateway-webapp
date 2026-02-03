<?php

/**
 * TEST: App\Unit\Models\WebSocket\ExpBackoffDelay
 * @covers App\Unit\Models\WebSocket\ExpBackoffDelay
 * @phpVersion >= 8.2
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

namespace Tests\Unit\Models\WebSocket;

use App\Models\WebSocket\ExpBackoffDelay;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for ExpBackoffDelay class
 */
final class ExpBackoffDelayTest extends TestCase {

	private const MAX_DELAY = 60;

	/**
	 * Test data for getNext method, containing minimum delay, maximum delay, and counter for each case
	 */
	private const TEST_DATA = [
		[1.5, 2.5, 1],
		[3.021, 4.979, 2],
		[6.124, 9.876, 3],
		[12.579, 19.421, 4],
		[26.483, 37.517, 5],
		[54.0, 66.0, 6],
		[54.0, 66.0, 7],
		[54.0, 66.0, 8],
	];

	/**
	 * @var ExpBackoffDelay Delay generator
	 */
	private ExpBackoffDelay $delay;

	/**
	 * Tests the function to calculate next delay
	 */
	public function testGetNext(): void {
		foreach (self::TEST_DATA as $case) {
			$delay = $this->delay->getNext();
			Assert::true($delay >= $case[0]);
			Assert::true($delay <= $case[1]);
			Assert::same($case[2], $this->delay->getCounter());
		}
	}

	/**
	 * Tests the function to reset delay generator
	 */
	public function testReset(): void {
		Assert::same(0, $this->delay->getCounter());
		$this->delay->getNext();
		$this->delay->getNext();
		Assert::same(2, $this->delay->getCounter());
		$this->delay->reset();
		Assert::same(0, $this->delay->getCounter());
	}

	/**
	 * Sets up test environment
	 */
	protected function setUp(): void {
		$this->delay = new ExpBackoffDelay(self::MAX_DELAY);
	}

}

$test = new ExpBackoffDelayTest();
$test->run();
