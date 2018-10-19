<?php

/**
 * TEST: App\IqrfNetModule\Model\DpaRawManager
 * @covers App\IqrfNetModule\Model\DpaRawManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\IqrfNetModule\Model;

use App\CoreModule\Model\FileManager;
use App\CoreModule\Model\JsonFileManager;
use App\IqrfNetModule\Model\DpaRawManager;
use App\IqrfNetModule\Model\MessageIdManager;
use App\IqrfNetModule\Model\WebSocketClient;
use App\IqrfNetModule\Requests\DpaRequest;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for DPA Raw request and response manager
 */
class DpaRawManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var DpaRawManager DPA Raw request and response manager
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
		$path = __DIR__ . '/../../data/iqrf/';
		$this->fileManager = new FileManager($path);
		$this->jsonFileManager = new JsonFileManager($path);
		$msgIdManager = \Mockery::mock(MessageIdManager::class);
		$msgIdManager->shouldReceive('generate')->andReturn('1');
		$wsClient = new WebSocketClient($this->wsServer);
		$request = new DpaRequest($msgIdManager);
		$this->manager = new DpaRawManager($request,$wsClient);
	}

	/**
	 * Cleanup the test environment
	 */
	protected function tearDown(): void {
		\Mockery::close();
	}

	/**
	 * Test function to send RAW IQRF packet
	 * @todo Add IQRF Gateway Daemon's WS server emulator
	 */
//	public function testSend(): void {
//		$packet = '01.00.06.03.ff.ff';
//		$array = [
//			'mType' => 'iqrfRaw',
//			'data' => [
//				'req' => [
//					'rData' => $packet,
//				],
//				'returnVerbose' => true,
//				'msgId' => '1',
//			],
//		];
//		$expected = [
//			'request' => Json::encode($array, Json::PRETTY),
//			'response' => Json::encode($array, Json::PRETTY),
//		];
//		Assert::equal($expected, $this->manager->send($packet));
//	}

	/**
	 * Test function to validation of raw IQRF packet (invalid packet)
	 */
	public function testValidatePacketInvalid(): void {
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
	public function testValidatePacketValid(): void {
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
	public function testUpdateNadr(): void {
		$packet = '01.00.06.03.ff.ff';
		$nadr = 'F';
		$expected = '0f.00.06.03.ff.ff';
		$this->manager->updateNadr($packet, $nadr);
		Assert::same($expected, $packet);
	}


	/**
	 * Test function to parse DPA response (broadcast)
	 */
	public function testParseResponseBroadcast(): void {
		$array = [
			'request' => $this->fileManager->read('request-broadcast.json'),
			'response' => $this->fileManager->read('response-broadcast.json'),
		];
		Assert::null($this->manager->parseResponse($array));
	}

	/**
	 * Test function to parse DPA response (Coordinator parser)
	 */
	public function testParseResponseCoordinator(): void {
		$array = ['response' => $this->fileManager->read('response-coordinator-bonded.json')];
		$expected = $this->jsonFileManager->read('data-coordinator-bonded');
		$actual = $this->manager->parseResponse($array);
		Assert::equal($expected, $actual);
	}

	/**
	 * Test function to parse DPA response (Enumeration parser)
	 */
	public function testParseResponseEnumerationParser(): void {
		$array = ['response' => $this->fileManager->read('response-enumeration.json')];
		$expected = $this->jsonFileManager->read('data-enumeration');
		$actual = $this->manager->parseResponse($array);
		Assert::equal($expected, $actual);
	}

	/**
	 * Test function to parse DPA response (OS parser)
	 */
	public function testParseResponseOsParser(): void {
		$array = ['response' => $this->fileManager->read('response-os-read.json')];
		$expected = $this->jsonFileManager->read('data-os-read');
		$actual = $this->manager->parseResponse($array);
		Assert::equal($expected, $actual);
	}

	/**
	 * Test function to parse DPA response (without parser)
	 */
	public function testParseResponseWithoutParser(): void {
		$array = ['response' => $this->fileManager->read('response-ledr-on.json')];
		Assert::null($this->manager->parseResponse($array));
	}

	/**
	 * Test function to get a DPA packet from JSON DPA request
	 */
	public function testGetPacketRequest(): void {
		$expected = '00.00.02.00.ff.ff';
		$array = ['request' => $this->fileManager->read('request-os-read.json')];
		$actual = $this->manager->getPacket($array, 'request');
		Assert::same($expected, $actual);
	}

	/**
	 * Test function to get a DPA packet from JSON DPA response
	 */
	public function testGetPacketResponse(): void {
		$expected = '00.00.02.80.00.00.00.00.dc.3c.10.81.42.24.b8.08.00.28.00.31';
		$array = ['response' => $this->fileManager->read('response-os-read.json')];
		$actual = $this->manager->getPacket($array, 'response');
		Assert::same($expected, $actual);
	}

}

$test = new DpaRawManagerTest($container);
$test->run();
