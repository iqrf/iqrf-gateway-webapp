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
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

class IqrfAppManagerTest extends TestCase {

	private $container;

	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * @test
	 * Test function to validation of raw IQRF packet
	 */
	public function testValidatePacket() {
		$commandManager = new CommandManager(false);
		$coordinatorParser = new CoordinatorParser();
		$osParser = new OsParser();
		$iqrfAppManager = new IqrfAppManager($commandManager, $coordinatorParser, $osParser);
		$validPackets = [
			'01.00.06.03.ff.ff', '01 00 06 03 ff ff',
			'01.00.06.03.ff.ff.', '01 00 06 03 ff ff.',
		];
		$invalidPackets = [
			';01.00.06.03.ff.ff', ';01 00 06 03 ff ff', '01.00.06.03.ff.ff;',
			'01 00 06 03 ff ff;', '; echo Test > test.log',
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
		$coordinatorParser = new CoordinatorParser();
		$osParser = new OsParser();
		$iqrfAppManager = new IqrfAppManager($commandManager, $coordinatorParser, $osParser);
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
		$expected = 'sudo iqrfapp raw ' . $packet;
		$commandManager = \Mockery::mock(CommandManager::class);
		$commandManager->shouldReceive('send')->with('iqrfapp raw ' . $packet, true)->andReturn($expected);
		$coordinatorParser = new CoordinatorParser();
		$osParser = new OsParser();
		$iqrfAppManager = new IqrfAppManager($commandManager, $coordinatorParser, $osParser);
		Assert::equal($expected, $iqrfAppManager->sendRaw($packet));
	}

	/**
	 * @test
	 * Test function to parse DPA response
	 */
	public function testParseResponse() {
		$commandManager = new CommandManager(false);
		$coordinatorParser = new CoordinatorParser();
		$osParser = new OsParser();
		$iqrfAppManager = new IqrfAppManager($commandManager, $coordinatorParser, $osParser);
		$packetCoordinatorBonded = 'raw 00.00.00.82.00.00.00.31.3e.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00 STATUS_NO_ERROR';
		$arrayCoordinatorBonded = $iqrfAppManager->parseResponse($packetCoordinatorBonded);
		$expectedCoordinatorBonded = [
			'BondedNodes' => [
				['0', '1', '1', '1', '1', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
			],
		];
		Assert::equal($expectedCoordinatorBonded, $arrayCoordinatorBonded);
		$packetCoordinatorBondedDiscovered = 'raw 00.00.00.81.00.00.00.31.3c.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00 STATUS_NO_ERROR';
		$arrayCoordinatorDiscovered = $iqrfAppManager->parseResponse($packetCoordinatorBondedDiscovered);
		$expectedCoordinatorDiscovered = [
			'DiscoveredNodes' => [
				['0', '0', '1', '1', '1', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
			],
		];
		Assert::equal($expectedCoordinatorDiscovered, $arrayCoordinatorDiscovered);
		$packetOsRead = 'raw 00.00.02.80.00.00.00.00.05.a4.00.81.38.24.79.08.00.28.00.f0 STATUS_NO_ERROR';
		$arrayOsRead = $iqrfAppManager->parseResponse($packetOsRead);
		$expectedOsRead = [
			'ModuleId' => '8100A405', 'OsVersion' => '3.08D',
			'TrType' => 'DCTR-72D', 'McuType' => 'PIC16F1938',
			'OsBuild' => '7908', 'Rssi' => '00',
			'SupplyVoltage' => '3.00 V', 'Flags' => '00',
			'SlotLimits' => 'f0',
		];
		Assert::equal($expectedOsRead, $arrayOsRead);
		$packetLedrOn = 'raw 01.00.06.81.02.00.00.33 STATUS_NO_ERROR';
		$arrayLedrOn = $iqrfAppManager->parseResponse($packetLedrOn);
		Assert::null($arrayLedrOn);
		$packetIoTKitSe = 'raw 00.00.5e.81.00.00.03.00 ERROR_PNUM';
		$arrayIoTKitSe = $iqrfAppManager->parseResponse($packetIoTKitSe);
		Assert::null($arrayIoTKitSe);
		$packetEmpty0 = 'raw STATUS_NO_ERROR';
		$arrayEmpty0 = $iqrfAppManager->parseResponse($packetEmpty0);
		Assert::null($arrayEmpty0);
		$packetEmpty1 = 'raw  STATUS_NO_ERROR';
		$arrayEmpty1 = $iqrfAppManager->parseResponse($packetEmpty1);
		Assert::null($arrayEmpty1);
	}

}

$test = new IqrfAppManagerTest($container);
$test->run();
