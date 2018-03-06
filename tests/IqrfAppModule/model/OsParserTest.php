<?php

/**
 * TEST: App\IqrfAppModule\Model\OsParser
 * @covers App\IqrfAppModule\Model\OsParser
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types=1);

namespace Test\IqrfAppModule\Model;

use App\IqrfAppModule\Model\OsParser;
use App\Model\JsonFileManager;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

class OsParserTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $jsonFileManager;

	/**
	 * @var OsParser DPA OS response parser
	 */
	private $parser;

	/**
	 * @var string OS Read info packet
	 */
	private $packetOsInfo;

	/**
	 * @var array Expected OS Read info parsed response
	 */
	private $expectedOsInfo;

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
		$this->parser = new OsParser();
		$this->jsonFileManager = new JsonFileManager(__DIR__ . '/data/');
		$this->packetOsInfo = $this->jsonFileManager->read('response-os-read')['response'];
		$this->expectedOsInfo = $this->jsonFileManager->read('data-os-read');
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

	/**
	 * @test
	 * Test function to get RF band from HWP configuration
	 */
	public function testGetRfBand() {
		Assert::equal('868 MHz', $this->parser->getRfBand('30'));
		Assert::equal('916 MHz', $this->parser->getRfBand('31'));
		Assert::equal('433 MHz', $this->parser->getRfBand('32'));
	}

	/**
	 * @test
	 * Test function to parse response to DPA OS - "Read HWP configuration" request
	 */
	public function testParseHwpConfiguration() {
		$packet = '00.00.02.82.00.00.00.00.b1.c9.12.34.34.36.00.34.33.34.32.37.34.34.34.34.34.36.00.34.34.34.34.34.34.34.34.34.34.34.37.34.c3.30';
		$actual = $this->parser->parseHwpConfiguration($packet);
		$expected = [
			'checksum' => 'b1',
			'configuration' => [
				'c9', '12', '34', '34', '36', '00', '34', '33', '34', '32', '37',
				'34', '34', '34', '34', '34', '36', '00', '34', '34', '34', '34',
				'34', '34', '34', '34', '34', '34', '34', '37', '34',
			],
			'parsedConfiguration' => [
				'checksum' => 'fd',
				'mainChannelA' => 2,
				'mainChannelB' => 52,
				'secondChannelA' => 52,
				'secondChannelB' => 0,
				'rfOutputPower' => 7,
				'rxSignalFilter' => 0,
				'rfLpTimeout' => 6,
				'baudRate' => 9600,
			],
			'rfpgm' => 'c3',
			'rfBand' => '868 MHz',
		];
		Assert::same($expected, $actual);
	}

}

$test = new OsParserTest($container);
$test->run();
