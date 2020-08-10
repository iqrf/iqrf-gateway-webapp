<?php

/**
 * TEST: App\IqrfNetModule\Models\WebSocketClient
 * @covers App\IqrfNetModule\Models\WebSocketClient
 * @phpVersion >= 7.2
 * @testCase
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
	 * @var WebSocketClient IQRF App manager
	 */
	private $client;

	/**
	 * @var ApiRequest JSON API request
	 */
	private $request;

	/**
	 * URL to IQRF Gateway Daemon's WebSocket server
	 */
	private const WS_SERVER = 'wss://echo.websocket.org';

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
