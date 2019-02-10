<?php

/**
 * TEST: App\IqrfNetModule\Parsers\EnumerationParser
 * @covers App\IqrfNetModule\Parsers\EnumerationParser
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Test\Unit\IqrfNetModule\Models;

use App\IqrfNetModule\Parsers\EnumerationParser;
use Tester\Assert;
use Tests\Toolkit\TestCases\DpaParserTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for parser of DPA Enumeration responses
 */
class EnumerationParserTest extends DpaParserTestCase {

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
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->parser = new EnumerationParser();
		$this->packet = $this->readResponsePacket('response-enumeration');
		$this->expected = $this->fileManager->read('data-enumeration');
	}

	/**
	 * Tests the function to parse a DPA response
	 */
	public function testParse(): void {
		Assert::equal($this->expected, $this->parser->parse($this->packet));
	}

	/**
	 * Tests the function to parse a response to DPA Enumeration request
	 */
	public function testParsePeripheralEnumeration(): void {
		$actual = $this->parser->parsePeripheralEnumeration($this->packet);
		Assert::equal($this->expected, $actual);
	}

	/**
	 * Tests the function to get embedded peripherals
	 */
	public function testGetEmbeddedPers(): void {
		$actual = $this->parser->getEmbeddedPers($this->packet);
		Assert::equal($this->expected['EmbeddedPers'], $actual);
	}

}

$test = new EnumerationParserTest();
$test->run();
