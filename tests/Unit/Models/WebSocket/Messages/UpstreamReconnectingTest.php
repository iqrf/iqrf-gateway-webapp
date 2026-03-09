<?php

/**
 * TEST: App\Models\WebSocket\Messages\UpstreamReconnecting
 * @covers App\Models\WebSocket\Messages\UpstreamReconnecting
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
use App\Models\WebSocket\Messages\UpstreamReconnecting;
use DateTimeImmutable;
use DateTimeZone;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for UpstreamReconnecting class
 */
final class UpstreamReconnectingTest extends TestCase {

	/**
	 * Invalid token auth code
	 */
	private const ATTEMPT = 1;

	/**
	 * Delay before reconnecting
	 */
	private const DELAY = 1.375;

	/**
	 * Message timestamp
	 */
	private const TIMESTAMP = '2026-01-01T10:00:00+00:00';

	/**
	 * Message datetime
	 */
	private DateTimeImmutable $dt;

	/**
	 * @var UpstreamReconnecting Message object
	 */
	private UpstreamReconnecting $message;

	/**
	 * Tests the function to serialize message to JSON object
	 */
	public function testJsonSerialize(): void {
		Assert::equal(
			[
				'type' => ProxyMessageType::RECONNECTING->value,
				'timestamp' => self::TIMESTAMP,
				'data' => [
					'attempt' => self::ATTEMPT,
					'delay' => self::DELAY,
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
			'{"type":"upstream_reconnecting","timestamp":"2026-01-01T10:00:00+00:00","data":{"attempt":1,"delay":1.375}}',
			$this->message->toJsonString(),
		);
	}

	/**
	 * Sets up test environment
	 */
	protected function setUp(): void {
		$this->dt = DateTimeImmutable::createFromFormat(
			'Y-m-d H:i:s',
			sprintf('%04d-%02d-%02d %02d:%02d:%02d', 2026, 1, 1, 10, 0, 0),
			new DateTimeZone('UTC')
		);
		$this->message = new UpstreamReconnecting(self::ATTEMPT, self::DELAY, $this->dt);
	}

}

$test = new UpstreamReconnectingTest();
$test->run();
