<?php

/**
 * TEST: App\IqrfAppModule\Model\IqrfAppParser
 * @covers App\IqrfAppModule\Model\IqrfAppParser
 * @phpVersion >= 5.6
 * @testCase
 */

declare(strict_types=1);

namespace Test\IqrfAppModule\Model;

use App\IqrfAppModule\Model\OsParser;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

class OsParserTest extends TestCase {

	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var OsParser
	 */
	private $parser;

	/**
	 * @var string OS Read info packet
	 */
	private $packetOsInfo = '00.00.02.80.00.00.00.00.05.a4.00.81.38.24.79.08.00.28.00.f0';

	/**
	 * @var array Expected OS Read info parsed response
	 */
	private $expectedOsInfo = [
		'ModuleId' => '8100A405',
		'OsVersion' => '3.08D',
		'TrType' => 'DCTR-72D',
		'McuType' => 'PIC16F1938',
		'OsBuild' => '0879',
		'Rssi' => '-130 dBm',
		'SupplyVoltage' => '3.00 V',
		'Flags' => '00',
		'SlotLimits' => 'f0',
	];

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
		$this->parser = new OsParser();
	}

	/**
	 * @test
	 * Test function to parse DPA response
	 */
	public function testParse() {
		$array = $this->parser->parse($this->packetOsInfo);
		Assert::equal($this->expectedOsInfo, $array);
	}

	/**
	 * @test
	 * Test function to parse response to DPA OS - "Read info" request
	 */
	public function testParseReadInfo() {
		$array = $this->parser->parseReadInfo($this->packetOsInfo);
		Assert::equal($this->expectedOsInfo, $array);
		$failPacket = preg_replace('/\.24\./', '\.ff\.', $this->packetOsInfo);
		$failArray = $this->parser->parseReadInfo($failPacket);
		$failExpected = $this->expectedOsInfo;
		$failExpected['TrType'] = $failExpected['McuType'] = 'UNKNOWN';
		Assert::equal($failExpected, $failArray);
	}

}

$test = new OsParserTest($container);
$test->run();
