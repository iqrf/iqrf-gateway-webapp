<?php

/**
 * TEST: App\IqrfAppModule\Model\WebSocketClient
 * @covers App\IqrfAppModule\Model\WebSocketClient
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\IqrfAppModule\Model;

use App\IqrfAppModule\Exception as IqrfException;
use App\IqrfAppModule\Model\MessageIdManager;
use App\IqrfAppModule\Model\WebSocketClient;
use App\IqrfAppModule\Requests\ApiRequest;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for WebSocket client
 */
class WebSocketClientTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var WebSocketClient IQRF App manager
	 */
	private $client;

	/**
	 * @var \Mockery\MockInterface Mocked message ID manager
	 */
	private $msgIdManager;

	/**
	 * @var ApiRequest JSON API request
	 */
	private $request;

	/**
	 * @var string URL to IQRF Gateway Daemon's WebSocket server
	 */
	private $wsServer = 'ws://echo.socketo.me:9000';

	/**
	 * Constructor
	 * @param Container $container Nette Tester Container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * Test function to send JSON DPA request via WebSocket (success)
	 */
	public function testSendSyncSuccess(): void {
		$array = [
			'data' => [
				'msgId' => '1',
				'status' => 0,
			],
		];
		$expected = [
			'request' => $array,
			'response' => $array,
		];
		$this->request->setRequest($array);
		Assert::same($expected, $this->client->sendSync($this->request));
	}

	/**
	 * Test function to send JSON DPA request via WebSocket (timeout)
	 */
	public function testSendSyncTimeout(): void {
		Assert::exception(function (): void {
			$wsServer = 'ws://localhost:9000';
			$manager = new WebSocketClient($wsServer);
			$array = ['data' => ['msgId' => '1',],];
			$this->request->setRequest($array);
			$manager->sendSync($this->request, 1);
		}, IqrfException\EmptyResponseException::class);
	}

	/**
	 * Set up the test environment
	 */
	protected function setUp(): void {
		$msgIdManager = \Mockery::mock(MessageIdManager::class);
		$msgIdManager->shouldReceive('generate')->andReturn('1');
		$this->request = new ApiRequest($msgIdManager);
		$this->client = new WebSocketClient($this->wsServer);
	}

	/**
	 * Cleanup the test environment
	 */
	protected function tearDown(): void {
		\Mockery::close();
	}

}

$test = new WebSocketClientTest($container);
$test->run();
