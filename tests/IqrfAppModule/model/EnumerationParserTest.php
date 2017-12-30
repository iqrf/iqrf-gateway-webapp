<?php

/**
 * TEST: App\IqrfAppModule\Model\IqrfAppParser
 * @covers App\IqrfAppModule\Model\IqrfAppParser
 * @phpVersion >= 5.6
 * @testCase
 */
declare(strict_types=1);

namespace Test\IqrfAppModule\Model;

use App\IqrfAppModule\Model\EnumerationParser;
use App\Model\JsonFileManager;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

class EnumerationParserTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $jsonFileManager;

	/**
	 * @var EnumerationParser DPA Enumeration response parser
	 */
	private $parser;

	/**
	 * @var string Enumeration packet
	 */
	private $packetEnumeration;

	/**
	 * @var array Expected Enumeration parsed response
	 */
	private $expectedEnumeration;

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
		$this->parser = new EnumerationParser();
		$this->jsonFileManager = new JsonFileManager(__DIR__ . '/data/');
		$this->packetEnumeration = $this->jsonFileManager->read('response-enumeration')['response'];
		$this->expectedEnumeration = $this->jsonFileManager->read('data-enumeration');
	}

	/**
	 * @test
	 * Test function to parse DPA response
	 */
	public function testParse() {
		$array = $this->parser->parse($this->packetEnumeration);
		Assert::equal($this->expectedEnumeration, $array);
	}

	/**
	 * @test
	 * Test function to parse response to DPA Enumeration request
	 */
	public function testParsePeripheralEnumeration() {
		$array = $this->parser->parsePeripheralEnumeration($this->packetEnumeration);
		Assert::equal($this->expectedEnumeration, $array);
	}
	
	/**
	 * @test
	 * Test function to get embedded peripherals
	 */
	public function testGetEmbeddedPers() {
		$array = $this->parser->getEmbeddedPers($this->packetEnumeration);
		Assert::equal($this->expectedEnumeration['EmbeddedPers'], $array);
	}

}

$test = new EnumerationParserTest($container);
$test->run();
