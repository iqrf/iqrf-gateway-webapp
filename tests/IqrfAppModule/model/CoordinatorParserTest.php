<?php

/**
 * TEST: App\IqrfAppModule\Model\CoordinatorParser
 * @covers App\IqrfAppModule\Model\CoordinatorParser
 * @phpVersion >= 5.6
 * @testCase
 */

namespace Test\IqrfAppModule\Model;

use App\IqrfAppModule\Model\CoordinatorParser;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

class CoordinatorParserTest extends TestCase {

	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var CoordinatorParser
	 */
	private $parser;

	/**
	 * @var string Coordinator Get Bonded nodes packet
	 */
	private $packetBonded = '00.00.00.82.00.00.00.31.3e.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00';

	/**
	 * @var string Coordinator Get Discovered nodes packet
	 */
	private $packetDiscovered = '00.00.00.81.00.00.00.31.3c.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00.00';

	/**
	 * @var array Expected Coordinator Get Bonded nodes parsed packet
	 */
	private $expectedBonded = [
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

	/**
	 * @var array Expected Coordinator Get Discovered nodes parsed packet
	 */
	private $expectedDiscovered = [
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
		$this->parser = new CoordinatorParser();
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
