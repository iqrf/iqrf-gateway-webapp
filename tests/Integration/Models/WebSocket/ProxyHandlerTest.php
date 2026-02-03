<?php

/**
 * TEST: App\Models\WebSocket\ProxyHandler
 * @covers App\Models\WebSocket\ProxyHandler
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

namespace Tests\Integration\Models\WebSocket;

use App\Models\WebSocket\Enums\ProxyAuthError;
use App\Models\WebSocket\Enums\ProxyMessageType;
use Nette\Utils\Json;
use Ratchet\Client\Connector;
use Ratchet\Client\WebSocket;
use Ratchet\RFC6455\Messaging\Frame;
use Ratchet\RFC6455\Messaging\MessageInterface;
use stdClass;
use Tester\Assert;
use Tester\TestCase;
use Tests\Toolkit\Traits\TWebSocketClientConnector;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for proxy handler class
 */
final class ProxyHandlerTest extends TestCase {

	use TWebSocketClientConnector;

	/**
	 * Port where server listens
	 */
	private const LISTENING_PORT = 9595;

	/**
	 * Server URL without token
	 */
	private const SERVER_URL = 'ws://127.0.0.1:9595';

	/**
	 * Server URL with invalid format auth token
	 */
	private const SERVER_URL_INVALID_FORMAT_TOKEN = self::SERVER_URL . '?token=invalidFormatToken';

	/**
	 * Server URL with invalid auth token
	 */
	private const SERVER_URL_INVALID_TOKEN = self::SERVER_URL . '?token=invalidToken';

	/**
	 * Server URL with valid auth token
	 */
	private const SERVER_URL_VALID_TOKEN = self::SERVER_URL . '?token=validToken';

	/**
	 * Client connector
	 */
	private Connector $connector;

	/**
	 * Tests the function to handle incoming connections without token
	 */
	public function testConnectWithoutToken(): void {
		$loop = $this->loop;
		$received = null;
		($this->connector)(self::SERVER_URL)->then(
			function (WebSocket $client) use (&$received, $loop): void {
				$client->on('message', function (MessageInterface $msg) use (&$received): void {
					$received = Json::decode($msg->getPayload());
				});
				$client->on('close', function (int $code, string $reason) use ($loop): void {
					Assert::same(Frame::CLOSE_POLICY, $code);
					$loop->stop();
				});
			}
		);
		$loop->run();
		assert($received instanceof stdClass);
		Assert::type(stdClass::class, $received);
		Assert::same(ProxyMessageType::PROXY_AUTH_FAILED->value, $received->type);
		Assert::same(ProxyAuthError::MISSING_TOKEN->value, $received->data->code);
	}

	/**
	 * Tests the function to handle incoming connections with invalid token format
	 */
	public function testConnectWithInvalidFormatToken(): void {
		$loop = $this->loop;
		$received = null;
		($this->connector)(self::SERVER_URL_INVALID_FORMAT_TOKEN)->then(
			function (WebSocket $client) use (&$received, $loop): void {
				$client->on('message', function (MessageInterface $msg) use (&$received): void {
					$received = Json::decode($msg->getPayload());
				});
				$client->on('close', function (int $code, string $reason) use ($loop): void {
					Assert::same(Frame::CLOSE_POLICY, $code);
					$loop->stop();
				});
			}
		);
		$loop->run();
		assert($received instanceof stdClass);
		Assert::type(stdClass::class, $received);
		Assert::same(ProxyMessageType::PROXY_AUTH_FAILED->value, $received->type);
		Assert::same(ProxyAuthError::INVALID_TOKEN->value, $received->data->code);
	}

	/**
	 * Tests the function to handle incoming connections with invaldi token
	 */
	public function testConnectWithInvalidToken(): void {
		$loop = $this->loop;
		$received = null;
		($this->connector)(self::SERVER_URL_INVALID_TOKEN)->then(
			function (WebSocket $client) use (&$received, $loop): void {
				$client->on('message', function (MessageInterface $msg) use (&$received): void {
					$received = Json::decode($msg->getPayload());
				});
				$client->on('close', function (int $code, string $reason) use ($loop): void {
					Assert::same(Frame::CLOSE_POLICY, $code);
					$loop->stop();
				});
			}
		);
		$loop->run();
		assert($received instanceof stdClass);
		Assert::type(stdClass::class, $received);
		Assert::same(ProxyMessageType::PROXY_AUTH_FAILED->value, $received->type);
		Assert::same(ProxyAuthError::INVALID_TOKEN->value, $received->data->code);
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->connector = $this->createConnector();
	}

	/**
	 * Tears down the test environment
	 */
	protected function tearDown(): void {
		$this->cleanupConnector();
	}

}

$test = new ProxyHandlerTest();
$test->run();
