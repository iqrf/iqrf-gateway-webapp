<?php

/**
 * TEST: App\IqrfAppModule\Model\IqrfAppManager
 * @covers App\IqrfAppModule\Model\IqrfAppManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types=1);

namespace Test\IqrfAppModule\Model;

use App\IqrfAppModule\Model\CoordinatorParser;
use App\IqrfAppModule\Model\EmptyResponseException;
use App\IqrfAppModule\Model\EnumerationParser;
use App\IqrfAppModule\Model\InvalidOperationModeException;
use App\IqrfAppModule\Model\IqrfAppManager;
use App\IqrfAppModule\Model\OsParser;
use App\Model\CommandManager;
use App\Model\FileManager;
use App\Model\JsonFileManager;
use DateTime;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

class IqrfAppManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

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
		$this->commandManager = new CommandManager(false);
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
		$iqrfAppManager = new IqrfAppManager($this->commandManager, $this->coordinatorParser, $this->osParser, $this->enumParser);
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
		$iqrfAppManager = new IqrfAppManager($this->commandManager, $this->coordinatorParser, $this->osParser, $this->enumParser);
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
		$iqrfAppManager = new IqrfAppManager($this->commandManager, $this->coordinatorParser, $this->osParser, $this->enumParser);
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
			'iqrfapp "{\"ctype\":\"conf\",\"type\":\"mode\",\"cmd\":\"forwarding\"}"',
			'iqrfapp "{\"ctype\":\"conf\",\"type\":\"mode\",\"cmd\":\"operational\"}"',
			'iqrfapp "{\"ctype\":\"conf\",\"type\":\"mode\",\"cmd\":\"service\"}"',
		];
		$commandManager = \Mockery::mock(CommandManager::class);
		foreach ($outputSuccess as $output) {
			$commandManager->shouldReceive('send')->with($output, true)->andReturn(true);
		}
		$iqrfAppManager = new IqrfAppManager($commandManager, $this->coordinatorParser, $this->osParser, $this->enumParser);
		foreach ($modesSuccess as $mode) {
			Assert::true($iqrfAppManager->changeOperationMode($mode));
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
		$timeout = 1000;
		$now = new DateTime();
		$cmdRead = 'iqrfapp readonly timeout 200';
		$cmd = 'iqrfapp "{\"ctype\":\"dpa\",\"type\":\"raw\",\"msgid\":\"' . $now->getTimestamp() . ''
				. '\",\"timeout\":' . $timeout . ',\"request\":\"' . $packet . '\",'
				. '\"request_ts\":\"\",\"confirmation\":\"\",\"confirmation_ts\":\"\",'
				. '\"response\":\"\",\"response_ts\":\"\"}"';
		$iqrfapp = 'Received: {
    "ctype": "dpa",
    "type": "raw",
    "msgid": ' . $now->getTimestamp() . ',
    "request": "' . $packet . '",
    "request_ts": "2017-12-09T20:56:03.110923",
    "confirmation": "",
    "confirmation_ts": "",
    "response": "01.00.06.83.00.00.00.00",
    "response_ts": "2017-12-09T20:56:03.137869",
    "status": "STATUS_NO_ERROR"
}';
		$expected['response'] = '{
    "ctype": "dpa",
    "type": "raw",
    "msgid": ' . $now->getTimestamp() . ',
    "request": "' . $packet . '",
    "request_ts": "2017-12-09T20:56:03.110923",
    "confirmation": "",
    "confirmation_ts": "",
    "response": "01.00.06.83.00.00.00.00",
    "response_ts": "2017-12-09T20:56:03.137869",
    "status": "STATUS_NO_ERROR"
}';
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('send')->with($cmdRead, true)->andReturn(null);
		$commandManager->shouldReceive('send')->with($cmd, true)->andReturn($iqrfapp);
		$iqrfAppManager = new IqrfAppManager($commandManager, $this->coordinatorParser, $this->osParser, $this->enumParser);
		$actual = $iqrfAppManager->sendRaw($packet, $timeout);
		unset($actual['request']);
		Assert::equal($expected, $actual);
	}

	/**
	 * @test
	 * Test function to parse DPA response
	 */
	public function testParseResponse() {
		$iqrfAppManager = new IqrfAppManager($this->commandManager, $this->coordinatorParser, $this->osParser, $this->enumParser);
		$responseCoordinatorBonded['response'] = $this->fileManager->read('response-coordinator-bonded.json');
		$arrayCoordinatorBonded = $iqrfAppManager->parseResponse($responseCoordinatorBonded);
		$expectedCoordinatorBonded = $this->jsonFileManager->read('data-coordinator-bonded');
		Assert::equal($expectedCoordinatorBonded, $arrayCoordinatorBonded);
		$responseCoordinatorBondedDiscovered['response'] = $this->fileManager->read('response-coordinator-discovered.json');
		$arrayCoordinatorDiscovered = $iqrfAppManager->parseResponse($responseCoordinatorBondedDiscovered);
		$expectedCoordinatorDiscovered = $this->jsonFileManager->read('data-coordinator-discovered');
		Assert::equal($expectedCoordinatorDiscovered, $arrayCoordinatorDiscovered);
		$responseOsRead['response'] = $this->fileManager->read('response-os-read.json');
		$arrayOsRead = $iqrfAppManager->parseResponse($responseOsRead);
		$expectedOsRead = $this->jsonFileManager->read('data-os-read');
		Assert::equal($expectedOsRead, $arrayOsRead);
		$responseEnumeration['response'] = $this->fileManager->read('response-enumeration.json');
		$arrayEnumeration = $iqrfAppManager->parseResponse($responseEnumeration);
		$expectedEnumeration = $this->jsonFileManager->read('data-enumeration');
		Assert::equal($expectedEnumeration, $arrayEnumeration);
		$packetLedrOn['response'] = $this->fileManager->read('response-ledr-on.json');
		$arrayLedrOn = $iqrfAppManager->parseResponse($packetLedrOn);
		Assert::null($arrayLedrOn);
		$packetIoTKitSe['response'] = $this->fileManager->read('response-error.json');
		$arrayIoTKitSe = $iqrfAppManager->parseResponse($packetIoTKitSe);
		Assert::null($arrayIoTKitSe);
		$packetBroadcast['request'] = $this->fileManager->read('request-broadcast.json');
		$packetBroadcast['response'] = $this->fileManager->read('response-broadcast.json');
		Assert::null($iqrfAppManager->parseResponse($packetBroadcast));
		Assert::exception(function () use ($iqrfAppManager) {
			$iqrfAppManager->parseResponse(['response' => '']);
		}, EmptyResponseException::class);
		Assert::exception(function () use ($iqrfAppManager) {
			$iqrfAppManager->parseResponse(['response' => '{"response": "","status":"STATUS_NO_ERROR"}']);
		}, EmptyResponseException::class);
	}

}

$test = new IqrfAppManagerTest($container);
$test->run();
