<?php

/**
 * TEST: App\IqrfAppModule\Model\IqrfAppManager
 * @covers App\IqrfAppModule\Model\IqrfAppManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\IqrfAppModule\Model;

use App\IqrfAppModule\Exception as IqrfException;
use App\IqrfAppModule\Model\IqrfAppManager;
use App\IqrfAppModule\Model\MessageIdManager;
use App\CoreModule\Model\FileManager;
use App\CoreModule\Model\JsonFileManager;
use Nette\DI\Container;
use Nette\Utils\Json;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for IQRF App manager
 */
class IqrfAppManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var IqrfAppManager IQRF App manager
	 */
	private $manager;

	/**
	 * @var FileManager Text file manager
	 */
	private $fileManager;

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $jsonFileManager;

	/**
	 * @var \Mockery\MockInterface Mocked message ID manager
	 */
	private $msgIdManager;

	/**
	 * @var string URL to IQRF Gateway Daemon's WebSocket server
	 */
	private $wsServer = 'ws://echo.socketo.me:9000';

	/**
	 * @var array DPA status exceptions
	 */
	private $statusExceptions = [
		-8 => IqrfException\ExclusiveAccessException::class,
		-7 => IqrfException\BadResponseException::class,
		-6 => IqrfException\BadRequestException::class,
		-5 => IqrfException\InterfaceBusyException::class,
		-4 => IqrfException\InterfaceErrorException::class,
		-3 => IqrfException\AbortedException::class,
		-2 => IqrfException\InterfaceQueueFullException::class,
		-1 => IqrfException\TimeoutException::class,
		1 => IqrfException\GeneralFailureException::class,
		2 => IqrfException\IncorrectPcmdException::class,
		3 => IqrfException\IncorrectPnumException::class,
		4 => IqrfException\IncorrectAddressException::class,
		5 => IqrfException\IncorrectDataLengthException::class,
		6 => IqrfException\IncorrectDataException::class,
		7 => IqrfException\IncorrectHwpidUsedException::class,
		8 => IqrfException\IncorrectNadrException::class,
		9 => IqrfException\CustomHandlerConsumedInterfaceDataException::class,
		10 => IqrfException\MissingCustomDpaHandlerException::class,
		11 => IqrfException\UserErrorException::class,
	];

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
	protected function setUp() {
		$path = __DIR__ . '/../../data/iqrf/';
		$this->fileManager = new FileManager($path);
		$this->jsonFileManager = new JsonFileManager($path);
		$this->msgIdManager = \Mockery::mock(MessageIdManager::class);
		$this->msgIdManager->shouldReceive('generate')->andReturn('1');
		$this->manager = new IqrfAppManager($this->wsServer, $this->msgIdManager);
	}

	/**
	 * Cleanup the test environment
	 */
	protected function tearDown() {
		\Mockery::close();
	}

	/**
	 * Change status in JSON DPA response
	 * @param array $json JSON DPA request and response
	 * @param int $status DPA status
	 */
	private function changeStatus(array &$json, int $status) {
		$data = Json::decode($json['response'], Json::FORCE_ARRAY);
		$data['data']['status'] = $status;
		$json['response'] = Json::encode($data);
	}

	/**
	 * Test function to send JSON DPA request via websocket (success)
	 */
	public function testSendToWebsocketSuccess() {
		$array = [
			'data' => [
				'msgId' => '1',
			],
		];
		$expected = '{"data":{"msgId":"1"}}';
		Assert::same($expected, $this->manager->sendToWebsocket($array));
	}

	/**
	 * Test function to send JSON DPA request via websocket (timeout)
	 */
	public function testSendToWebsocketTimeout() {
		Assert::exception(function() {
			$wsServer = 'ws://localhost:9000';
			$manager = new IqrfAppManager($wsServer, $this->msgIdManager);
			$array = ['data' => ['msgId' => '1',],];
			$manager->sendToWebsocket($array, 1);
		}, IqrfException\EmptyResponseException::class);
	}

	/**
	 * Test function to send RAW IQRF packet
	 */
	public function testSendRaw() {
		$packet = '01.00.06.03.ff.ff';
		$array = [
			'mType' => 'iqrfRaw',
			'data' => [
				'msgId' => '1',
				'req' => [
					'rData' => $packet,
				],
			],
			'returnVerbose' => true,
		];
		$expected = [
			'request' => Json::encode($array, Json::PRETTY),
			'response' => Json::encode($array),
		];
		Assert::equal($expected, $this->manager->sendRaw($packet));
	}

	/**
	 * Test function to change IQRF Gateway Daemon's operation mode (invalid mode)
	 */
	public function testChangeOperationModeInvalid() {
		Assert::exception(function() {
			$this->manager->changeOperationMode('invalid');
		}, IqrfException\InvalidOperationModeException::class);
	}

	/**
	 * Test function to change IQRF Gateway Daemon's operation mode (valid mode)
	 */
	public function testChangeOperationModeValid() {
		$modes = ['forwarding', 'operational', 'service'];
		$format = '{"mType":"mngDaemon_Mode","data":{"msgId":"1","req":{"operMode":"%s"}},"returnVerbose":true}';
		foreach ($modes as $mode) {
			Assert::same(sprintf($format, $mode), $this->manager->changeOperationMode($mode));
		}
	}

	/**
	 * Test function to validation of raw IQRF packet (invalid packet)
	 */
	public function testValidatePacketInvalid() {
		$packets = [
			'01 00 06 03 ff ff',
			'01 00 06 03 ff ff.',
			';01.00.06.03.ff.ff',
			';01 00 06 03 ff ff',
			'01.00.06.03.ff.ff;',
			'01 00 06 03 ff ff;',
			'; echo Test > test.log',
		];
		foreach ($packets as $packet) {
			Assert::false($this->manager->validatePacket($packet));
		}
	}

	/**
	 * Test function to validation of raw IQRF packet (valid packet)
	 */
	public function testValidatePacketValid() {
		$packets = [
			'01.00.06.03.ff.ff',
			'01.00.06.03.ff.ff.',
		];
		foreach ($packets as $packet) {
			Assert::true($this->manager->validatePacket($packet));
		}
	}

	/**
	 * Test function to update NADR in raw DPA packet
	 */
	public function testUpdateNadr() {
		$packet = '01.00.06.03.ff.ff';
		$nadr = 'F';
		$expected = '0f.00.06.03.ff.ff';
		$this->manager->updateNadr($packet, $nadr);
		Assert::same($expected, $packet);
	}

	/**
	 * Test function to fix NADR in raw DPA packet
	 */
	public function testFixPacket() {
		$packet = '00.01.06.03.ff.ff';
		$expected = '01.00.06.03.ff.ff';
		$this->manager->fixPacket($packet);
		Assert::same($expected, $packet);
	}

	/**
	 * Test function to parse DPA response (DPA error)
	 */
	public function testParseResponseError() {
		Assert::exception(function () {
			$array['response'] = $this->fileManager->read('response-error.json');
			$this->manager->parseResponse($array);
		}, IqrfException\TimeoutException::class);
	}

	/**
	 * Test function to parse DPA response (broadcast)
	 */
	public function testParseResponseBroadcast() {
		$array['request'] = $this->fileManager->read('request-broadcast.json');
		$array['response'] = $this->fileManager->read('response-broadcast.json');
		Assert::null($this->manager->parseResponse($array));
	}

	/**
	 * Test function to parse DPA response (Coordinator parser)
	 */
	public function testParseResponseCoordinator() {
		$array['response'] = $this->fileManager->read('response-coordinator-bonded.json');
		$expected = $this->jsonFileManager->read('data-coordinator-bonded');
		Assert::equal($expected, $this->manager->parseResponse($array));
	}

	/**
	 * Test function to parse DPA response (Enumeration parser)
	 */
	public function testParseResponseEnumerationParser() {
		$array['response'] = $this->fileManager->read('response-enumeration.json');
		$expected = $this->jsonFileManager->read('data-enumeration');
		Assert::equal($expected, $this->manager->parseResponse($array));
	}

	/**
	 * Test function to parse DPA response (OS parser)
	 */
	public function testParseResponseOsParser() {
		$array['response'] = $this->fileManager->read('response-os-read.json');
		$expected = $this->jsonFileManager->read('data-os-read');
		Assert::equal($expected, $this->manager->parseResponse($array));
	}

	/**
	 * Test function to parse DPA response (without parser)
	 */
	public function testParseResponseWithoutParser() {
		$array['response'] = $this->fileManager->read('response-ledr-on.json');
		Assert::null($this->manager->parseResponse($array));
	}

	/**
	 * Test function to check status from JSON response (status = OK)
	 */
	public function testCheckStatusOk() {
		Assert::noError(function () {
			$array['response'] = $this->fileManager->read('response-os-read.json');
			$this->manager->checkStatus($array);
		});
	}

	/**
	 * Test function to check staus from JSON response (status = DPA error)
	 */
	public function testCheckStatusError() {
		$array['response'] = $this->fileManager->read('response-error.json');
		foreach ($this->statusExceptions as $status => $exception) {
			$this->changeStatus($array, $status);
			Assert::exception(function() use ($array) {
				$this->manager->checkStatus($array);
			}, $exception);
		}
	}

	/**
	 * Test function to get a DPA packet from JSON DPA request
	 */
	public function testGetPacketRequest() {
		$expected = '00.00.02.00.ff.ff';
		$array['request'] = $this->fileManager->read('request-os-read.json');
		Assert::same($expected, $this->manager->getPacket($array, 'request'));
	}

	/**
	 * Test function to get a DPA packet from JSON DPA response
	 */
	public function testGetPacketResponse() {
		$expected = '00.00.02.80.00.00.00.00.dc.3c.10.81.42.24.b8.08.00.28.00.31';
		$array['response'] = $this->fileManager->read('response-os-read.json');
		Assert::same($expected, $this->manager->getPacket($array, 'response'));
	}

}

$test = new IqrfAppManagerTest($container);
$test->run();
