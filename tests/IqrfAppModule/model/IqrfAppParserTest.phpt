<?php

/**
 * TEST: App\IqrfAppModule\Model\IqrfAppParser
 * @phpVersion >= 5.6
 * @testCase
 */

namespace Test\IqrfAppModule\Model;

use App\IqrfAppModule\Model\IqrfAppParser;
use App\Model\CommandManager;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

class IqrfAppParserTest extends TestCase {

	private $container;

	function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * @test
	 * Test function to parse DPA response
	 */
	public function testParseResponse() {
		$commandManager = new CommandManager(false);
		$iqrfAppParser = new IqrfAppParser($commandManager);
		$packetCoordinatorBonded = 'raw 00.00.00.82.00.00.00.31.3e.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00 STATUS_NO_ERROR';
		$arrayCoordinatorBonded = $iqrfAppParser->parseResponse($packetCoordinatorBonded);
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
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0']
			]
		];
		Assert::equal($expectedCoordinatorBonded, $arrayCoordinatorBonded);
		$packetCoordinatorBondedDiscovered = 'raw 00.00.00.81.00.00.00.31.3c.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00 STATUS_NO_ERROR';
		$arrayCoordinatorDiscovered = $iqrfAppParser->parseResponse($packetCoordinatorBondedDiscovered);
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
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0']
			]
		];
		Assert::equal($expectedCoordinatorDiscovered, $arrayCoordinatorDiscovered);
		$packetOsRead = 'raw 00.00.02.80.00.00.00.00.05.a4.00.81.38.24.79.08.00.28.00.f0 STATUS_NO_ERROR';
		$arrayOsRead = $iqrfAppParser->parseResponse($packetOsRead);
		$expectedOsRead = [
			'ModuleId' => '8100A405', 'OsVersion' => '3.08D',
			'TrType' => 'DCTR-72D', 'McuType' => 'PIC16F1938',
			'OsBuild' => '7908', 'Rssi' => '00',
			'SupplyVoltage' => '3.00 V', 'Flags' => '00',
			'SlotLimits' => 'f0',
		];
		Assert::equal($expectedOsRead, $arrayOsRead);
		$packetLedrOn = 'raw 01.00.06.81.02.00.00.33 STATUS_NO_ERROR';
		$arrayLedrOn = $iqrfAppParser->parseResponse($packetLedrOn);
		Assert::null($arrayLedrOn);
		$packetIoTKitSe = 'raw 00.00.5e.81.00.00.03.00 ERROR_PNUM';
		$arrayIoTKitSe = $iqrfAppParser->parseResponse($packetIoTKitSe);
		Assert::null($arrayIoTKitSe);
		$packetEmpty0 = 'raw STATUS_NO_ERROR';
		$arrayEmpty0 = $iqrfAppParser->parseResponse($packetEmpty0);
		Assert::null($arrayEmpty0);
		$packetEmpty1 = 'raw  STATUS_NO_ERROR';
		$arrayEmpty1 = $iqrfAppParser->parseResponse($packetEmpty1);
		Assert::null($arrayEmpty1);
	}

	/**
	 * @test
	 * Test function to parse response to DPA Coordinator - "Get bonded nodes" and "Get discovered nodes" request
	 */
	public function testParseCoordinatorGetNodes() {
		$commandManager = new CommandManager(false);
		$iqrfAppParser = new IqrfAppParser($commandManager);
		$packetBonded = '00.00.00.82.00.00.00.31.3e.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00';
		$arrayBonded = $iqrfAppParser->parseCoordinatorGetNodes($packetBonded);
		$expectedBonded = [
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
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0']
			]
		];
		Assert::equal($expectedBonded, $arrayBonded);
		$packetDiscovered = '00.00.00.81.00.00.00.31.3c.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00';
		$arrayDiscovered = $iqrfAppParser->parseCoordinatorGetNodes($packetDiscovered);
		$expectedDiscovered = [
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
				['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0']
			]
		];
		Assert::equal($expectedDiscovered, $arrayDiscovered);
	}

	/**
	 * @test
	 * Test function to parse response to DPA OS - "Read info" request
	 */
	public function testParseOsReadInfo() {
		$commandManager = new CommandManager(false);
		$iqrfAppParser = new IqrfAppParser($commandManager);
		$packet = '00.00.02.80.00.00.00.00.05.a4.00.81.38.24.79.08.00.28.00.f0';
		$array = $iqrfAppParser->parseOsReadInfo($packet);
		$expected = [
			'ModuleId' => '8100A405', 'OsVersion' => '3.08D',
			'TrType' => 'DCTR-72D', 'McuType' => 'PIC16F1938',
			'OsBuild' => '7908', 'Rssi' => '00',
			'SupplyVoltage' => '3.00 V', 'Flags' => '00',
			'SlotLimits' => 'f0',
		];
		Assert::equal($expected, $array);
	}

}

$test = new IqrfAppParserTest($container);
$test->run();
