<?php

/**
 * TEST: App\IqrfNetModule\Models\WebSocketClient
 * @covers App\IqrfNetModule\Models\WebSocketClient
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

namespace Tests\Integration\IqrfNetModule\Models;

use App\IqrfNetModule\Exceptions as IqrfException;
use App\IqrfNetModule\Models\MessageIdManager;
use App\IqrfNetModule\Models\WebSocketClient;
use App\IqrfNetModule\Requests\ApiRequest;
use Mockery;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for WebSocket client
 */
final class WebSocketClientTest extends TestCase {

	/**
	 * URL to IQRF Gateway Daemon's WebSocket server
	 */
	private const WS_SERVER = 'ws://ws-echo.iqrf.org/';

	/**
	 * @var WebSocketClient IQRF App manager
	 */
	private WebSocketClient $client;

	/**
	 * @var ApiRequest JSON API request
	 */
	private ApiRequest $request;

	/**
	 * Tests the function to send a JSON DPA request via WebSocket (success)
	 */
	public function testSendSyncSuccess(): void {
		$array = (object) [
			'mType' => 'test',
			'data' => (object) [
				'msgId' => '1',
				'status' => 0,
			],
		];
		$expected = [
			'request' => $array,
			'response' => $array,
		];
		$this->request->set($array);
		Assert::equal($expected, $this->client->sendSync($this->request));
	}

	/**
	 * Tests the function to send a JSON DPA request via WebSocket (timeout)
	 */
	public function testSendSyncTimeout(): void {
		Assert::exception(function (): void {
			$wsServer = 'ws://localhost:9000';
			$manager = new WebSocketClient($wsServer);
			$array = ['data' => ['msgId' => '1']];
			$this->request->set($array);
			$manager->sendSync($this->request, true, 1);
		}, IqrfException\EmptyResponseException::class);
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$msgIdManager = Mockery::mock(MessageIdManager::class);
		$msgIdManager->shouldReceive('generate')->andReturn('1');
		$this->request = new ApiRequest($msgIdManager);
		$this->client = new WebSocketClient(self::WS_SERVER);
	}

	/**
	 * Cleanups the test environment
	 */
	protected function tearDown(): void {
		Mockery::close();
	}

}

$test = new WebSocketClientTest();
$test->run();
