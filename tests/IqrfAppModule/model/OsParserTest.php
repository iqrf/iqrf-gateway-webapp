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
		$this->packetOsInfo= $this->jsonFileManager->read('response-os-read')['response'];
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

}

$test = new OsParserTest($container);
$test->run();
