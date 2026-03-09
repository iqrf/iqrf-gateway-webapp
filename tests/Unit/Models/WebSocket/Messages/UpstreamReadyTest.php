<?php

/**
 * TEST: App\Models\WebSocket\Messages\UpstreamReady
 * @covers App\Models\WebSocket\Messages\UpstreamReady
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
use App\Models\WebSocket\Messages\UpstreamReady;
use DateTimeImmutable;
use DateTimeZone;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for UpstreamReady class
 */
final class UpstreamReadyTest extends TestCase {

	/**
	 * Upstream session expiration timestamp
	 */
	private const EXPIRATION = '2027-01-01T12:00:00+00:00';

	/**
	 * Message timestamp
	 */
	private const TIMESTAMP = '2026-01-01T10:00:00+00:00';

	/**
	 * Message datetime
	 */
	private DateTimeImmutable $dt;

	/**
	 * @var UpstreamReady Message object
	 */
	private UpstreamReady $message;

	/**
	 * Tests the function to serialize message to JSON object
	 */
	public function testJsonSerialize(): void {
		Assert::equal(
			[
				'type' => ProxyMessageType::READY->value,
				'timestamp' => self::TIMESTAMP,
				'data' => [
					'expiration' => self::EXPIRATION,
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
			'{"type":"upstream_ready","timestamp":"2026-01-01T10:00:00+00:00","data":{"expiration":"2027-01-01T12:00:00+00:00"}}',
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
		$this->message = new UpstreamReady(self::EXPIRATION, $this->dt);
	}

}

$test = new UpstreamReadyTest();
$test->run();
