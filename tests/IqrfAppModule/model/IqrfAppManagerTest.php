<?php

/**
 * TEST: App\IqrfAppModule\Model\IqrfAppManager
 * @covers App\IqrfAppModule\Model\IqrfAppManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\IqrfAppModule\Model;

use App\IqrfAppModule\Model\InvalidOperationModeException;
use App\IqrfAppModule\Model\IqrfAppManager;
use App\IqrfAppModule\Model\MessageIdManager;
use App\IqrfAppModule\Model\TimeoutException;
use App\Model\FileManager;
use App\Model\JsonFileManager;
use Nette\DI\Container;
use Nette\Utils\Json;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

class IqrfAppManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var IqrfAppManager IQRF App manager
	 */
	private $iqrfAppManager;

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
		$this->iqrfAppManager = new IqrfAppManager($this->wsServer, $this->msgIdManager);
	}

	/**
	 * Test function to validation of raw IQRF packet
	 */
	public function testValidatePacket() {
		$validPackets = [
			'01.00.06.03.ff.ff',
			'01.00.06.03.ff.ff.',
		];
		$invalidPackets = [
			'01 00 06 03 ff ff',
			'01 00 06 03 ff ff.',
			';01.00.06.03.ff.ff',
			';01 00 06 03 ff ff',
			'01.00.06.03.ff.ff;',
			'01 00 06 03 ff ff;',
			'; echo Test > test.log',
		];
		foreach ($validPackets as $packet) {
			Assert::true($this->iqrfAppManager->validatePacket($packet));
		}
		foreach ($invalidPackets as $packet) {
			Assert::false($this->iqrfAppManager->validatePacket($packet));
		}
	}

	/**
	 * Test function to update NADR in raw DPA packet
	 */
	public function testUpdateNadr() {
		$packet = '01.00.06.03.ff.ff';
		$nadr = 'F';
		$expected = '0f.00.06.03.ff.ff';
		Assert::same($expected, $this->iqrfAppManager->updateNadr($packet, $nadr));
	}

	/**
	 * Test function to fix NADR in raw DPA packet
	 */
	public function testFixPacket() {
		$packet = '00.01.06.03.ff.ff';
		$expected = '01.00.06.03.ff.ff';
		Assert::same($expected, $this->iqrfAppManager->fixPacket($packet));
	}

	/**
	 * Test function to change iqrf-daemon operation mode
	 */
	public function testChangeOperationMode() {
		$modesSuccess = ['forwarding', 'operational', 'service'];
		$format = '{"mType":"mngDaemon_Mode","data":{"msgId":"1","req":{"operMode":"%s"}},"returnVerbose":true}';
		foreach ($modesSuccess as $mode) {
			Assert::same(sprintf($format, $mode), $this->iqrfAppManager->changeOperationMode($mode));
		}
		Assert::exception(function() {
			$this->iqrfAppManager->changeOperationMode('invalid');
		}, InvalidOperationModeException::class);
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
		Assert::equal($expected, $this->iqrfAppManager->sendRaw($packet));
	}

	/**
	 * Test function to parse DPA response
	 */
	public function testParseResponse() {
		$responseCoordinatorBonded['response'] = $this->fileManager->read('response-coordinator-bonded.json');
		$expectedCoordinatorBonded = $this->jsonFileManager->read('data-coordinator-bonded');
		Assert::equal($expectedCoordinatorBonded, $this->iqrfAppManager->parseResponse($responseCoordinatorBonded));
		$responseCoordinatorDiscovered['response'] = $this->fileManager->read('response-coordinator-discovered.json');
		$expectedCoordinatorDiscovered = $this->jsonFileManager->read('data-coordinator-discovered');
		Assert::equal($expectedCoordinatorDiscovered, $this->iqrfAppManager->parseResponse($responseCoordinatorDiscovered));
		$responseOsRead['response'] = $this->fileManager->read('response-os-read.json');
		$expectedOsRead = $this->jsonFileManager->read('data-os-read');
		Assert::equal($expectedOsRead, $this->iqrfAppManager->parseResponse($responseOsRead));
		$responseEnumeration['response'] = $this->fileManager->read('response-enumeration.json');
		$expectedEnumeration = $this->jsonFileManager->read('data-enumeration');
		Assert::equal($expectedEnumeration, $this->iqrfAppManager->parseResponse($responseEnumeration));
		$packetLedrOn['response'] = $this->fileManager->read('response-ledr-on.json');
		Assert::null($this->iqrfAppManager->parseResponse($packetLedrOn));
		$packetBroadcast['request'] = $this->fileManager->read('request-broadcast.json');
		$packetBroadcast['response'] = $this->fileManager->read('response-broadcast.json');
		Assert::null($this->iqrfAppManager->parseResponse($packetBroadcast));
		Assert::exception(function () {
			$timeout['response'] = $this->fileManager->read('response-error.json');
			$this->iqrfAppManager->parseResponse($timeout);
		}, TimeoutException::class);
	}

}

$test = new IqrfAppManagerTest($container);
$test->run();
