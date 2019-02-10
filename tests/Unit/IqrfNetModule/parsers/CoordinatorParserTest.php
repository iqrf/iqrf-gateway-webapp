<?php

/**
 * TEST: App\IqrfNetModule\Parsers\CoordinatorParser
 * @covers App\IqrfNetModule\Parsers\CoordinatorParser
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Test\Unit\IqrfNetModule\Parsers;

use App\IqrfNetModule\Parsers\CoordinatorParser;
use Tester\Assert;
use Tests\Toolkit\TestCases\DpaParserTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for parser for of Coordinator responses
 */
class CoordinatorParserTest extends DpaParserTestCase {

	/**
	 * @var CoordinatorParser DPA Coordinator response parser
	 */
	private $parser;

	/**
	 * @var string[] DPA packets
	 */
	private $packet = [];

	/**
	 * @var mixed[] Expected parsed information
	 */
	private $expected;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->parser = new CoordinatorParser();
		$this->packet['bonded'] = $this->readResponsePacket('response-coordinator-bonded');
		$this->packet['discovered'] = $this->readResponsePacket('response-coordinator-discovered');
		$this->expected['bonded'] = $this->fileManager->read('data-coordinator-bonded');
		$this->expected['discovered'] = $this->fileManager->read('data-coordinator-discovered');
	}

	/**
	 * Tests the function to parse a DPA response "Get bonded Nodes"
	 */
	public function testParseBonded(): void {
		Assert::equal($this->expected['bonded'], $this->parser->parse($this->packet['bonded']));
	}

	/**
	 * Tests the function to parse a DPA response "Get discovered Node"
	 */
	public function testParseDiscovered(): void {
		Assert::equal($this->expected['discovered'], $this->parser->parse($this->packet['discovered']));
	}

	/**
	 * Tests the function to parse a response to DPA Coordinator - "Get bonded nodes" request
	 */
	public function testParseGetBondedNodes(): void {
		$actual = $this->parser->parseGetNodes($this->packet['bonded']);
		Assert::equal($this->expected['bonded'], $actual);
	}

	/**
	 * Tests the function to parse a response to DPA Coordinator - "Get discovered nodes" request
	 */
	public function testParseGetDiscoveredNodes(): void {
		$actual = $this->parser->parseGetNodes($this->packet['discovered']);
		Assert::equal($this->expected['discovered'], $actual);
	}

}

$test = new CoordinatorParserTest();
$test->run();
