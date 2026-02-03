<?php

/**
 * TEST: App\Models\WebSocket\Messages\UpstreamAuthFailed
 * @covers App\Models\WebSocket\Messages\UpstreamAuthFailed
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

namespace Tests\Unit\Models\WebSocket\Messages;

use App\Models\WebSocket\Enums\ProxyMessageType;
use App\Models\WebSocket\Messages\UpstreamAuthFailed;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for UpstreamAuthFailed class
 */
final class UpstreamAuthFailedTest extends TestCase {

	/**
	 * Invalid token auth code
	 */
	private const AUTH_CODE = 4;

	/**
	 * Message timestamp
	 */
	private const TIMESTAMP = 1767261600;

	/**
	 * @var UpstreamAuthFailed Message object
	 */
	private UpstreamAuthFailed $message;

	/**
	 * Tests the function to serialize message to JSON object
	 */
	public function testJsonSerialize(): void {
		Assert::equal(
			[
				'type' => ProxyMessageType::UPSTREAM_AUTH_FAILED->value,
				'timestamp' => self::TIMESTAMP,
				'data' => [
					'code' => self::AUTH_CODE,
				],
			],
			$this->message->jsonSerialize(),
		);
	}

	/**
	 * Tests the function to serialize message to JSON string
	 */
	public function testToJsonString(): void {
		Assert::same(
			'{"type":"upstream_auth_failed","timestamp":1767261600,"data":{"code":4}}',
			$this->message->toJsonString(),
		);
	}

	/**
	 * Sets up test environment
	 */
	protected function setUp(): void {
		$this->message = new UpstreamAuthFailed(self::AUTH_CODE, self::TIMESTAMP);
	}

}

$test = new UpstreamAuthFailedTest();
$test->run();
