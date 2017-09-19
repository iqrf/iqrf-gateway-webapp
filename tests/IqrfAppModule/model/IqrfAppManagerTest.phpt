<?php

/**
 * TEST: App\IqrfAppModule\Model\IqrfAppManager
 * @phpVersion >= 5.6
 * @testCase
 */

namespace Test\IqrfAppModule\Model;

use App\IqrfAppModule\Model\CoordinatorParser;
use App\IqrfAppModule\Model\IqrfAppManager;
use App\IqrfAppModule\Model\OsParser;
use App\Model\CommandManager;
use App\Model\FileManager;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

class IqrfAppManagerTest extends TestCase {

	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var CommandManager
	 */
	private $commandManager;

	/**
	 * @var FileManager
	 */
	private $fileManager;

	/**
	 * @var CoordinatorParser
	 */
	private $coordinatorParser;

	/**
	 * @var OsParser
	 */
	private $osParser;

	/**
	 * Constructor
	 * @param Container $container
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
		$this->coordinatorParser = new CoordinatorParser();
		$this->osParser = new OsParser();
	}

	/**
	 * @test
	 * Test function to validation of raw IQRF packet
	 */
	public function testValidatePacket() {
		$iqrfAppManager = new IqrfAppManager($this->commandManager, $this->coordinatorParser, $this->osParser);
		$validPackets = [
			'01.00.06.03.ff.ff',
			'01 00 06 03 ff ff',
			'01.00.06.03.ff.ff.',
			'01 00 06 03 ff ff.',
		];
		$invalidPackets = [
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
	 * Test function to change iqrf-daemon operation mode
	 */
	public function testChangeOperationMode() {
		$modesSuccess = ['forwarding', 'operational', 'service'];
		$outputSuccess = [
			'iqrfapp "{\"ctype\": \"conf\",\"type\": \"mode\",\"cmd\": \"forwarding\"}"',
			'iqrfapp "{\"ctype\": \"conf\",\"type\": \"mode\",\"cmd\": \"operational\"}"',
			'iqrfapp "{\"ctype\": \"conf\",\"type\": \"mode\",\"cmd\": \"service\"}"',
		];
		$commandManager = \Mockery::mock(CommandManager::class);
		foreach ($outputSuccess as $output) {
			$commandManager->shouldReceive('send')->with($output, true)->andReturn(true);
		}
		$iqrfAppManager = new IqrfAppManager($commandManager, $this->coordinatorParser, $this->osParser);
		foreach ($modesSuccess as $mode) {
			Assert::true($iqrfAppManager->changeOperationMode($mode));
		}
		Assert::null($iqrfAppManager->changeOperationMode('invalid'));
	}

	/**
	 * @test
	 * Test function to send RAW IQRF packet
	 */
	public function testSendRaw() {
		$packet = '01.00.06.03.ff.ff';
		$timeout = 1000;
		$cmd = 'iqrfapp "{\"ctype\":\"dpa\",\"type\":\"raw\",\"msgid\":\"1\",'
				. '\"timeout\":' . $timeout . ',\"request\":\"' . $packet . '\",'
				. '\"request_ts\":\"\",\"confirmation\":\"\",\"confirmation_ts\":\"\",'
				. '\"response\":\"\",\"response_ts\":\"\"}"';
		$expected = 'sudo ' . $cmd;
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('send')->with($cmd, true)->andReturn($expected);
		$iqrfAppManager = new IqrfAppManager($commandManager, $this->coordinatorParser, $this->osParser);
		Assert::equal($expected, $iqrfAppManager->sendRaw($packet, $timeout));
	}

	/**
	 * @test
	 * Test function to parse DPA response
	 */
	public function testParseResponse() {
		$iqrfAppManager = new IqrfAppManager($this->commandManager, $this->coordinatorParser, $this->osParser);
		$responseCoordinatorBonded = $this->fileManager->read('response-coordinator-bonded.json');
		$arrayCoordinatorBonded = $iqrfAppManager->parseResponse($responseCoordinatorBonded);
		$expectedCoordinatorBonded = [
			'BondedNodes' => [
				['0', '1', '1', '1', '1', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
			],
		];
		Assert::equal($expectedCoordinatorBonded, $arrayCoordinatorBonded);
		$responseCoordinatorBondedDiscovered = $this->fileManager->read('response-coordinator-discovered.json');
		$arrayCoordinatorDiscovered = $iqrfAppManager->parseResponse($responseCoordinatorBondedDiscovered);
		$expectedCoordinatorDiscovered = [
			'DiscoveredNodes' => [
				['0', '0', '1', '1', '1', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
			],
		];
		Assert::equal($expectedCoordinatorDiscovered, $arrayCoordinatorDiscovered);
		$responseOsRead = $this->fileManager->read('response-os-read.json');
		$arrayOsRead = $iqrfAppManager->parseResponse($responseOsRead);
		$expectedOsRead = [
			'ModuleId' => '8100A405',
			'OsVersion' => '3.08D',
			'TrType' => 'DCTR-72D',
			'McuType' => 'PIC16F1938',
			'OsBuild' => '7908',
			'Rssi' => '00',
			'SupplyVoltage' => '3.00 V',
			'Flags' => '00',
			'SlotLimits' => 'f0',
		];
		Assert::equal($expectedOsRead, $arrayOsRead);
		$packetLedrOn = $this->fileManager->read('response-ledr-on.json');
		$arrayLedrOn = $iqrfAppManager->parseResponse($packetLedrOn);
		Assert::null($arrayLedrOn);
		$packetIoTKitSe = $this->fileManager->read('response-error.json');
		$arrayIoTKitSe = $iqrfAppManager->parseResponse($packetIoTKitSe);
		Assert::null($arrayIoTKitSe);
		$emptyResponse = $iqrfAppManager->parseResponse('');
		Assert::null($emptyResponse);
	}

}

$test = new IqrfAppManagerTest($container);
$test->run();
