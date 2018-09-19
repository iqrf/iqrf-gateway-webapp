<?php

/**
 * TEST: App\IqrfAppModule\Model\WebsocketClient
 * @covers App\IqrfAppModule\Model\WebsocketClient
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\IqrfAppModule\Model;

use App\IqrfAppModule\Exception as IqrfException;
use App\IqrfAppModule\Model\MessageIdManager;
use App\IqrfAppModule\Model\WebsocketClient;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for websocket client
 */
class WebsocketClientTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var WebsocketClient IQRF App manager
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
	 * Set up the test environment
	 */
	protected function setUp(): void {
		$this->msgIdManager = \Mockery::mock(MessageIdManager::class);
		$this->msgIdManager->shouldReceive('generate')->andReturn('1');
		$this->client = new WebsocketClient($this->wsServer, $this->msgIdManager);
	}

	/**
	 * Cleanup the test environment
	 */
	protected function tearDown(): void {
		\Mockery::close();
	}

	/**
	 * Test function to send JSON DPA request via websocket (success)
	 */
	public function testSendSyncSuccess(): void {
		$expected = [
			'data' => [
				'msgId' => '1',
			],
		];
		Assert::same($expected, $this->client->sendSync($expected));
	}

	/**
	 * Test function to send JSON DPA request via websocket (timeout)
	 */
	public function testSendSyncTimeout(): void {
		Assert::exception(function(): void {
			$wsServer = 'ws://localhost:9000';
			$manager = new WebsocketClient($wsServer, $this->msgIdManager);
			$array = ['data' => ['msgId' => '1',],];
			$manager->sendSync($array, 1);
		}, IqrfException\EmptyResponseException::class);
	}

}

$test = new WebsocketClientTest($container);
$test->run();
