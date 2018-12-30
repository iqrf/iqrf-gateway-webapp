<?php

/**
 * TEST: App\IqrfNetModule\Models\GwModeManager
 * @covers App\IqrfNetModule\Models\GwModeManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\IqrfNetModule\Models;

use App\IqrfNetModule\Exceptions as IqrfException;
use App\IqrfNetModule\Models\GwModeManager;
use App\IqrfNetModule\Models\MessageIdManager;
use App\IqrfNetModule\Models\WebSocketClient;
use App\IqrfNetModule\Requests\ApiRequest;
use Mockery;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for IQRF App manager
 */
class GwModeManagerTest extends TestCase {

	/**
	 * @var GwModeManager IQRF Gateway Daemon's mode manager
	 */
	private $manager;

	/**
	 * @var string URL to IQRF Gateway Daemon's WebSocket server
	 */
	private $wsServer = 'ws://echo.socketo.me:9000';

	/**
	 * Test function to change IQRF Gateway Daemon's operation mode (invalid mode)
	 */
	public function testChangeOperationModeInvalid(): void {
		Assert::exception(function (): void {
			$this->manager->changeMode('invalid');
		}, IqrfException\InvalidOperationModeException::class);
	}

	/**
	 * Test function to change IQRF Gateway Daemon's operation mode (valid mode)
	 * @todo Add IQRF Gateway Daemon's WS server emulator
	 */
//	public function testChangeOperationModeValid(): void {
//		$modes = ['forwarding', 'operational', 'service'];
//		foreach ($modes as $mode) {
//			$array = [
//				'mType' => 'mngDaemon_Mode',
//				'data' => [
//					'req' => ['operMode' => $mode],
//					'returnVerbose' => true,
//					'msgId' => '1',
//				],
//			];
//			$expected = ['request' => $array, 'response' => $array];
//			Assert::same($expected, $this->manager->changeMode($mode));
//		}
//	}

	/**
	 * Set up the test environment
	 */
	protected function setUp(): void {
		$msgIdManager = Mockery::mock(MessageIdManager::class);
		$msgIdManager->shouldReceive('generate')->andReturn('1');
		$wsClient = new WebSocketClient($this->wsServer);
		$request = new ApiRequest($msgIdManager);
		$this->manager = new GwModeManager($request, $wsClient);
	}

	/**
	 * Cleanup the test environment
	 */
	protected function tearDown(): void {
		Mockery::close();
	}

}

$test = new GwModeManagerTest();
$test->run();
