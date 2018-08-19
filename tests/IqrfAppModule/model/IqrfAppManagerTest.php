<?php

/**
 * TEST: App\IqrfAppModule\Model\IqrfAppManager
 * @covers App\IqrfAppModule\Model\IqrfAppManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\IqrfAppModule\Model;

use App\IqrfAppModule\Model\AbortedException;
use App\IqrfAppModule\Model\BadRequestException;
use App\IqrfAppModule\Model\BadResponseException;
use App\IqrfAppModule\Model\CustomHandlerConsumedInterfaceDataException;
use App\IqrfAppModule\Model\EmptyResponseException;
use App\IqrfAppModule\Model\ExclusiveAccessException;
use App\IqrfAppModule\Model\GeneralFailureException;
use App\IqrfAppModule\Model\IncorrectAddressException;
use App\IqrfAppModule\Model\IncorrectDataException;
use App\IqrfAppModule\Model\IncorrectDataLengthException;
use App\IqrfAppModule\Model\IncorrectHwpidUsedException;
use App\IqrfAppModule\Model\IncorrectNadrException;
use App\IqrfAppModule\Model\IncorrectPcmdException;
use App\IqrfAppModule\Model\IncorrectPnumException;
use App\IqrfAppModule\Model\InterfaceBusyException;
use App\IqrfAppModule\Model\InterfaceErrorException;
use App\IqrfAppModule\Model\InterfaceQueueFullException;
use App\IqrfAppModule\Model\InvalidOperationModeException;
use App\IqrfAppModule\Model\IqrfAppManager;
use App\IqrfAppModule\Model\MessageIdManager;
use App\IqrfAppModule\Model\MissingCustomDpaHandlerException;
use App\IqrfAppModule\Model\TimeoutException;
use App\IqrfAppModule\Model\UserErrorException;
use App\Model\FileManager;
use App\Model\JsonFileManager;
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
		-8 => ExclusiveAccessException::class,
		-7 => BadResponseException::class,
		-6 => BadRequestException::class,
		-5 => InterfaceBusyException::class,
		-4 => InterfaceErrorException::class,
		-3 => AbortedException::class,
		-2 => InterfaceQueueFullException::class,
		-1 => TimeoutException::class,
		1 => GeneralFailureException::class,
		2 => IncorrectPcmdException::class,
		3 => IncorrectPnumException::class,
		4 => IncorrectAddressException::class,
		5 => IncorrectDataLengthException::class,
		6 => IncorrectDataException::class,
		7 => IncorrectHwpidUsedException::class,
		8 => IncorrectNadrException::class,
		9 => CustomHandlerConsumedInterfaceDataException::class,
		10 => MissingCustomDpaHandlerException::class,
		11 => UserErrorException::class,
	];

	/**
	 * Constructor
	 * @param Container $container Nette Tester Container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * Set up test environment
	 */
	public function setUp() {
		$this->fileManager = new FileManager(__DIR__ . '/data/');
		$this->jsonFileManager = new JsonFileManager(__DIR__ . '/data/');
		$this->msgIdManager = \Mockery::mock(MessageIdManager::class);
		$this->msgIdManager->shouldReceive('generate')->andReturn('1');
		$this->manager = new IqrfAppManager($this->wsServer, $this->msgIdManager);
	}

	/**
	 * Change status in JSON DPA response
	 * @param array $json JSON DPA request and response
	 * @param int $status DPA status
	 */
	public function changeStatus(array &$json, int $status) {
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
		}, EmptyResponseException::class);
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
		}, InvalidOperationModeException::class);
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
		}, TimeoutException::class);
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
