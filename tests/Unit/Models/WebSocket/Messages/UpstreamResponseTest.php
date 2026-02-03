<?php

/**
 * TEST: App\Models\WebSocket\Messages\UpstreamResponse
 * @covers App\Models\WebSocket\Messages\UpstreamResponse
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
use App\Models\WebSocket\Messages\UpstreamResponse;
use Nette\Utils\Json;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for UpstreamResponse class
 */
final class UpstreamResponseTest extends TestCase {

	/**
	 * Message contents
	 */
	private const PAYLOAD = [
		'mType' => 'iqrfRaw',
		'data' => [
			'msgId' => '4e753420-3287-4de8-bdd5-ef2235d09a75',
			'rsp' => [
				'rData' => '00.00.06.83.00.00.00.44',
			],
			'insId' => 'iqrfgd2',
			'status' => 0,
		],
	];

	/**
	 * Message timestamp
	 */
	private const TIMESTAMP = 1767261600;

	/**
	 * @var UpstreamResponse Message object
	 */
	private UpstreamResponse $message;

	/**
	 * Tests the function to serialize message to JSON object
	 */
	public function testJsonSerialize(): void {
		Assert::equal(
			[
				'type' => ProxyMessageType::RESPONSE->value,
				'timestamp' => self::TIMESTAMP,
				'data' => Json::decode(Json::encode(self::PAYLOAD)),
			],
			$this->message->jsonSerialize(),
		);
	}

	/**
	 * Tests the function to serialize message to JSON string
	 */
	public function testToJsonString(): void {
		Assert::same(
			'{"type":"upstream_response","timestamp":1767261600,"data":{"mType":"iqrfRaw","data":{"msgId":"4e753420-3287-4de8-bdd5-ef2235d09a75","rsp":{"rData":"00.00.06.83.00.00.00.44"},"insId":"iqrfgd2","status":0}}}',
			$this->message->toJsonString(),
		);
	}

	/**
	 * Sets up test environment
	 */
	protected function setUp(): void {
		$this->message = new UpstreamResponse(
			message: Json::decode(Json::encode(self::PAYLOAD)),
			timestamp: self::TIMESTAMP,
		);
	}

}

$test = new UpstreamResponseTest();
$test->run();
