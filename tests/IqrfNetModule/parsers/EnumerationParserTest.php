<?php

/**
 * TEST: App\IqrfNetModule\Parsers\EnumerationParser
 * @covers App\IqrfNetModule\Parsers\EnumerationParser
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\IqrfNetModule\Models;

use App\CoreModule\Models\JsonFileManager;
use App\IqrfNetModule\Parsers\EnumerationParser;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for parser of DPA Enumeration responses
 */
class EnumerationParserTest extends TestCase {

	/**
	 * @var EnumerationParser DPA Enumeration response parser
	 */
	private $parser;

	/**
	 * @var string Enumeration packet
	 */
	private $packet;

	/**
	 * @var mixed[] Expected Enumeration parsed response
	 */
	private $expected;

	/**
	 * Set up test environment
	 */
	protected function setUp(): void {
		$this->parser = new EnumerationParser();
		$jsonFileManager = new JsonFileManager(__DIR__ . '/../../data/iqrf/');
		$this->packet = $jsonFileManager->read('response-enumeration')['data']['rsp']['rData'];
		$this->expected = $jsonFileManager->read('data-enumeration');
	}

	/**
	 * Test function to parse DPA response
	 */
	public function testParse(): void {
		Assert::equal($this->expected, $this->parser->parse($this->packet));
	}

	/**
	 * Test function to parse response to DPA Enumeration request
	 */
	public function testParsePeripheralEnumeration(): void {
		$actual = $this->parser->parsePeripheralEnumeration($this->packet);
		Assert::equal($this->expected, $actual);
	}

	/**
	 * Test function to get embedded peripherals
	 */
	public function testGetEmbeddedPers(): void {
		$actual = $this->parser->getEmbeddedPers($this->packet);
		Assert::equal($this->expected['EmbeddedPers'], $actual);
	}

}

$test = new EnumerationParserTest();
$test->run();
