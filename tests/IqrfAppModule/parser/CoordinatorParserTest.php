<?php

/**
 * TEST: App\IqrfAppModule\Parser\CoordinatorParser
 * @covers App\IqrfAppModule\Parser\CoordinatorParser
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\IqrfAppModule\Parser;

use App\IqrfAppModule\Parser\CoordinatorParser;
use App\Model\JsonFileManager;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for parser for of Coordinator responses
 */
class CoordinatorParserTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var CoordinatorParser DPA Coordinator response parser
	 */
	private $parser;

	/**
	 * @var string[] DPA packets
	 */
	private $packet = [];

	/**
	 * @var array Expected parsed information
	 */
	private $expected;

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
		$jsonFileManager = new JsonFileManager(__DIR__ . '/../../data/iqrf/');
		$this->packet['bonded'] = $jsonFileManager->read('response-coordinator-bonded')['data']['rsp']['rData'];
		$this->packet['discovered'] = $jsonFileManager->read('response-coordinator-discovered')['data']['rsp']['rData'];
		$this->expected['bonded'] = $jsonFileManager->read('data-coordinator-bonded');
		$this->expected['discovered'] = $jsonFileManager->read('data-coordinator-discovered');
	}

	/**
	 * Test function to parse DPA response "Get bonded Nodes"
	 */
	public function testParseBonded() {
		Assert::equal($this->expected['bonded'], $this->parser->parse($this->packet['bonded']));
	}

	/**
	 * Test function to parse DPA response "Get discovered Node"
	 */
	public function testParseDiscovered() {
		Assert::equal($this->expected['discovered'], $this->parser->parse($this->packet['discovered']));
	}

	/**
	 * Test function to parse response to DPA Coordinator - "Get bonded nodes" request
	 */
	public function testParseGetBondedNodes() {
		$actual = $this->parser->parseGetNodes($this->packet['bonded']);
		Assert::equal($this->expected['bonded'], $actual);
	}

	/**
	 * Test function to parse response to DPA Coordinator - "Get discovered nodes" request
	 */
	public function testParseGetDiscoveredNodes() {
		$actual = $this->parser->parseGetNodes($this->packet['discovered']);
		Assert::equal($this->expected['discovered'], $actual);
	}

}

$test = new CoordinatorParserTest($container);
$test->run();
