<?php

/**
 * TEST: App\Models\WebSocket\Messages\ProxySessionRefreshSuccess
 * @covers App\Models\WebSocket\Messages\ProxySessionRefreshSuccess
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
use App\Models\WebSocket\Messages\ProxySessionRefreshSuccess;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for ProxySessionRefreshSuccess class
 */
final class ProxySessionRefreshSuccessTest extends TestCase {

	/**
	 * Message timestamp
	 */
	private const TIMESTAMP = 1767261600;

	/**
	 * @var ProxySessionRefreshSuccess Message object
	 */
	private ProxySessionRefreshSuccess $message;

	/**
	 * Tests the function to serialize message to JSON object
	 */
	public function testJsonSerialize(): void {
		Assert::equal(
			[
				'type' => ProxyMessageType::PROXY_SESSION_REFRESH_SUCCESS->value,
				'timestamp' => self::TIMESTAMP,
			],
			$this->message->jsonSerialize(),
		);
	}

	/**
	 * Tests the function to serialize message to JSON string
	 */
	public function testToJsonString(): void {
		Assert::same(
			'{"type":"proxy_session_refresh_success","timestamp":1767261600}',
			$this->message->toJsonString(),
		);
	}

	/**
	 * Sets up test environment
	 */
	protected function setUp(): void {
		$this->message = new ProxySessionRefreshSuccess(self::TIMESTAMP);
	}

}

$test = new ProxySessionRefreshSuccessTest();
$test->run();
