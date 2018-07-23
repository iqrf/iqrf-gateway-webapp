<?php

/**
 * TEST: App\IqrfAppModule\Model\IqrfAppManager
 * @covers App\IqrfAppModule\Model\IqrfAppManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\IqrfAppModule\Model;

use App\IqrfAppModule\Model\CoordinatorParser;
use App\IqrfAppModule\Model\EmptyResponseException;
use App\IqrfAppModule\Model\EnumerationParser;
use App\IqrfAppModule\Model\InvalidOperationModeException;
use App\IqrfAppModule\Model\IqrfAppManager;
use App\IqrfAppModule\Model\OsParser;
use App\Model\FileManager;
use App\Model\JsonFileManager;
use DateTime;
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
	 * @var FileManager Text file manager
	 */
	private $fileManager;

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $jsonFileManager;

	/**
	 * @var CoordinatorParser DPA Coordinator response parser
	 */
	private $coordinatorParser;

	/**
	 * @var EnumerationParser DPA Enumeration response parser
	 */
	private $enumParser;

	/**
	 * @var OsParser DPA OS response parser
	 */
	private $osParser;

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
		$this->coordinatorParser = new CoordinatorParser();
		$this->enumParser = new EnumerationParser();
		$this->osParser = new OsParser();
	}

	/**
	 * @test
	 * Test function to validation of raw IQRF packet
	 */
	public function testValidatePacket() {
		$iqrfAppManager = new IqrfAppManager($this->wsServer, $this->coordinatorParser, $this->osParser, $this->enumParser);
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
			Assert::true($iqrfAppManager->validatePacket($packet));
		}
		foreach ($invalidPackets as $packet) {
			Assert::false($iqrfAppManager->validatePacket($packet));
		}
	}

	/**
	 * @test
	 * Test function to update NADR in raw DPA packet
	 */
	public function testUpdateNadr() {
		$iqrfAppManager = new IqrfAppManager($this->wsServer, $this->coordinatorParser, $this->osParser, $this->enumParser);
		$packet = '01.00.06.03.ff.ff';
		$nadr = 'F';
		$expected = '0f.00.06.03.ff.ff';
		Assert::same($expected, $iqrfAppManager->updateNadr($packet, $nadr));
	}

	/**
	 * @test
	 * Test function to fix NADR in raw DPA packet
	 */
	public function testFixPacket() {
		$iqrfAppManager = new IqrfAppManager($this->wsServer, $this->coordinatorParser, $this->osParser, $this->enumParser);
		$packet = '00.01.06.03.ff.ff';
		$expected = '01.00.06.03.ff.ff';
		Assert::same($expected, $iqrfAppManager->fixPacket($packet));
	}

	/**
	 * @test
	 * Test function to change iqrf-daemon operation mode
	 */
	public function testChangeOperationMode() {
		$modesSuccess = ['forwarding', 'operational', 'service'];
		$outputSuccess = [
			'{"ctype":"conf","type":"mode","cmd":"forwarding"}',
			'{"ctype":"conf","type":"mode","cmd":"operational"}',
			'{"ctype":"conf","type":"mode","cmd":"service"}',
		];
		$iqrfAppManager = new IqrfAppManager($this->wsServer, $this->coordinatorParser, $this->osParser, $this->enumParser);
		foreach ($modesSuccess as $key => $mode) {
			Assert::same($outputSuccess[$key], $iqrfAppManager->changeOperationMode($mode));
		}
		Assert::exception(function() use ($iqrfAppManager) {
			$iqrfAppManager->changeOperationMode('invalid');
		}, InvalidOperationModeException::class);
	}

	/**
	 * @test
	 * Test function to send RAW IQRF packet
	 */
	public function testSendRaw() {
		$packet = '01.00.06.03.ff.ff';
		$now = new DateTime();
		$array = [
			'mType' => 'iqrfRaw',
			'data' => [
				'msgId' => (string) $now->getTimestamp(),
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
		$iqrfAppManager = new IqrfAppManager($this->wsServer, $this->coordinatorParser, $this->osParser, $this->enumParser);
		Assert::equal($expected, $iqrfAppManager->sendRaw($packet));
	}

	/**
	 * @test
	 * Test function to parse DPA response
	 */
	public function testParseResponse() {
		$iqrfAppManager = new IqrfAppManager($this->wsServer, $this->coordinatorParser, $this->osParser, $this->enumParser);
		$responseCoordinatorBonded['response'] = $this->fileManager->read('response-coordinator-bonded.json');
		$expectedCoordinatorBonded = $this->jsonFileManager->read('data-coordinator-bonded');
		Assert::equal($expectedCoordinatorBonded, $iqrfAppManager->parseResponse($responseCoordinatorBonded));
		$responseCoordinatorDiscovered['response'] = $this->fileManager->read('response-coordinator-discovered.json');
		$expectedCoordinatorDiscovered = $this->jsonFileManager->read('data-coordinator-discovered');
		Assert::equal($expectedCoordinatorDiscovered, $iqrfAppManager->parseResponse($responseCoordinatorDiscovered));
		$responseOsRead['response'] = $this->fileManager->read('response-os-read.json');
		$expectedOsRead = $this->jsonFileManager->read('data-os-read');
		Assert::equal($expectedOsRead, $iqrfAppManager->parseResponse($responseOsRead));
		$responseEnumeration['response'] = $this->fileManager->read('response-enumeration.json');
		$expectedEnumeration = $this->jsonFileManager->read('data-enumeration');
		Assert::equal($expectedEnumeration, $iqrfAppManager->parseResponse($responseEnumeration));
		$packetLedrOn['response'] = $this->fileManager->read('response-ledr-on.json');
		Assert::null($iqrfAppManager->parseResponse($packetLedrOn));
		$packetIoTKitSe['response'] = $this->fileManager->read('response-error.json');
		Assert::null($iqrfAppManager->parseResponse($packetIoTKitSe));
		$packetBroadcast['request'] = $this->fileManager->read('request-broadcast.json');
		$packetBroadcast['response'] = $this->fileManager->read('response-broadcast.json');
		Assert::null($iqrfAppManager->parseResponse($packetBroadcast));
		Assert::exception(function () use ($iqrfAppManager) {
			$iqrfAppManager->parseResponse(['response' => '']);
		}, EmptyResponseException::class);
	}

}

$test = new IqrfAppManagerTest($container);
$test->run();
