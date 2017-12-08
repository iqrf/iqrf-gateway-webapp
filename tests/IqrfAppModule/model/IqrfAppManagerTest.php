<?php

/**
 * TEST: App\IqrfAppModule\Model\IqrfAppManager
 * @covers App\IqrfAppModule\Model\IqrfAppManager
 * @phpVersion >= 5.6
 * @testCase
 */

namespace Test\IqrfAppModule\Model;

use App\IqrfAppModule\Model\CoordinatorParser;
use App\IqrfAppModule\Model\EmptyResponseException;
use App\IqrfAppModule\Model\InvalidOperationModeException;
use App\IqrfAppModule\Model\IqrfAppManager;
use App\IqrfAppModule\Model\OsParser;
use App\Model\CommandManager;
use App\Model\FileManager;
use DateTime;
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
	 * Test function to change iqrf-daemon operation mode
	 */
	public function testChangeOperationMode() {
		$modesSuccess = ['forwarding', 'operational', 'service'];
		$outputSuccess = [
			'iqrfapp "{\"ctype\":\"conf\",\"type\":\"mode\",\"cmd\":\"forwarding\"}" > /dev/null 2>&1',
			'iqrfapp "{\"ctype\":\"conf\",\"type\":\"mode\",\"cmd\":\"operational\"}" > /dev/null 2>&1',
			'iqrfapp "{\"ctype\":\"conf\",\"type\":\"mode\",\"cmd\":\"service\"}" > /dev/null 2>&1',
		];
		$commandManager = \Mockery::mock(CommandManager::class);
		foreach ($outputSuccess as $output) {
			$commandManager->shouldReceive('send')->with($output, true)->andReturn(true);
		}
		$iqrfAppManager = new IqrfAppManager($commandManager, $this->coordinatorParser, $this->osParser);
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
		$cmd = 'iqrfapp "{\"ctype\":\"dpa\",\"type\":\"raw\",\"msgid\":\"' . $now->getTimestamp()
				. '\",\"timeout\":' . $timeout . ',\"request\":\"' . $packet . '\",'
				. '\"request_ts\":\"\",\"confirmation\":\"\",\"confirmation_ts\":\"\",'
				. '\"response\":\"\",\"response_ts\":\"\"}" > /dev/null 2>&1';
		$expected['response'] = 'sudo ' . $cmd;
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('send')->with($cmdRead, true)->andReturn('sudo ' . $cmdRead);
		$commandManager->shouldReceive('send')->with($cmd, true)->andReturn('sudo ' . $cmd);
		$iqrfAppManager = new IqrfAppManager($commandManager, $this->coordinatorParser, $this->osParser);
		$actual = $iqrfAppManager->sendRaw($packet, $timeout);
		unset($actual['request']);
		Assert::equal($expected, $actual);
	}

	/**
	 * @test
	 * Test function to parse DPA response
	 */
	public function testParseResponse() {
		$iqrfAppManager = new IqrfAppManager($this->commandManager, $this->coordinatorParser, $this->osParser);
		$responseCoordinatorBonded['response'] = $this->fileManager->read('response-coordinator-bonded.json');
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
		$responseCoordinatorBondedDiscovered['response'] = $this->fileManager->read('response-coordinator-discovered.json');
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
		$responseOsRead['response'] = $this->fileManager->read('response-os-read.json');
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
		$packetLedrOn['response'] = $this->fileManager->read('response-ledr-on.json');
		$arrayLedrOn = $iqrfAppManager->parseResponse($packetLedrOn);
		Assert::null($arrayLedrOn);
		$packetIoTKitSe['response'] = $this->fileManager->read('response-error.json');
		$arrayIoTKitSe = $iqrfAppManager->parseResponse($packetIoTKitSe);
		Assert::null($arrayIoTKitSe);
		Assert::exception(function () use ($iqrfAppManager) {
			$iqrfAppManager->parseResponse(['response' => '']);
		}, EmptyResponseException::class);
	}

}

$test = new IqrfAppManagerTest($container);
$test->run();
