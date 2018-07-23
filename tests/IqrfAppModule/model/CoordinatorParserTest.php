<?php

/**
 * TEST: App\IqrfAppModule\Model\CoordinatorParser
 * @covers App\IqrfAppModule\Model\CoordinatorParser
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types=1);

namespace Test\IqrfAppModule\Model;

use App\IqrfAppModule\Model\CoordinatorParser;
use App\Model\JsonFileManager;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

class CoordinatorParserTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $jsonFileManager;

	/**
	 * @var CoordinatorParser DPA Coordinator response parser
	 */
	private $parser;

	/**
	 * @var string Coordinator Get Bonded nodes packet
	 */
	private $packetBonded;

	/**
	 * @var string Coordinator Get Discovered nodes packet
	 */
	private $packetDiscovered;

	/**
	 * @var array Expected Coordinator Get Bonded nodes parsed packet
	 */
	private $expectedBonded;

	/**
	 * @var array Expected Coordinator Get Discovered nodes parsed packet
	 */
	private $expectedDiscovered;

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
		$this->parser = new CoordinatorParser();
		$this->jsonFileManager = new JsonFileManager(__DIR__ . '/data/');
		$this->packetBonded = $this->jsonFileManager->read('response-coordinator-bonded')['data']['rsp']['rData'];
		$this->packetDiscovered = $this->jsonFileManager->read('response-coordinator-discovered')['data']['rsp']['rData'];
		$this->expectedBonded = $this->jsonFileManager->read('data-coordinator-bonded');
		$this->expectedDiscovered = $this->jsonFileManager->read('data-coordinator-discovered');
	}

	/**
	 * @test
	 * Test function to parse DPA response
	 */
	public function testParse() {
		$arrayBonded = $this->parser->parse($this->packetBonded);
		Assert::equal($this->expectedBonded, $arrayBonded);
		$arrayDiscovered = $this->parser->parse($this->packetDiscovered);
		Assert::equal($this->expectedDiscovered, $arrayDiscovered);
	}

	/**
	 * @test
	 * Test function to parse response to DPA Coordinator - "Get bonded nodes" and "Get discovered nodes" request
	 */
	public function testParseGetNodes() {
		$arrayBonded = $this->parser->parseGetNodes($this->packetBonded);
		Assert::equal($this->expectedBonded, $arrayBonded);
		$arrayDiscovered = $this->parser->parseGetNodes($this->packetDiscovered);
		Assert::equal($this->expectedDiscovered, $arrayDiscovered);
	}

}

$test = new CoordinatorParserTest($container);
$test->run();
