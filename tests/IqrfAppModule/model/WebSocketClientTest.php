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
			],
		];
		$expected = [
			'request' => $array,
			'response' => $array,
		];
		Assert::same($expected, $this->client->sendSync($array));
	}

	/**
	 * Test function to send JSON DPA request via WebSocket (timeout)
	 */
	public function testSendSyncTimeout(): void {
		Assert::exception(function (): void {
			$wsServer = 'ws://localhost:9000';
			$manager = new WebSocketClient($wsServer, $this->msgIdManager);
			$array = ['data' => ['msgId' => '1',],];
			$manager->sendSync($array, 1);
		}, IqrfException\EmptyResponseException::class);
	}

	/**
	 * Set up the test environment
	 */
	protected function setUp(): void {
		$this->msgIdManager = \Mockery::mock(MessageIdManager::class);
		$this->msgIdManager->shouldReceive('generate')->andReturn('1');
		$this->client = new WebSocketClient($this->wsServer, $this->msgIdManager);
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
