<?php

/**
 * TEST: App\IqrfNetModule\Parsers\OsParser
 * @covers App\IqrfNetModule\Parsers\OsParser
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\IqrfNetModule\Parsers;

use App\IqrfNetModule\Parsers\OsParser;
use Tester\Assert;
use Tests\Toolkit\TestCases\DpaParserTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for parser of DPA OS responses
 */
class OsParserTest extends DpaParserTestCase {

	/**
	 * @var OsParser DPA OS response parser
	 */
	private $parser;

	/**
	 * @var string[] DPA packets
	 */
	private $packet;

	/**
	 * @var mixed[] Expected parsed responses
	 */
	private $expected;

	/**
	 * Sets up the test environment
	 */
	public function setUp(): void {
		parent::setUp();
		$this->parser = new OsParser();
		$this->packet['hwpConfiguration'] = $this->readResponsePacket('response-os-hwp-config');
		$this->packet['osInfo'] = $this->readResponsePacket('response-os-read');
		$this->expected['hwpConfiguration'] = $this->fileManager->read('data-os-hwp-config');
		$this->expected['osInfo'] = $this->fileManager->read('data-os-read');
	}

	/**
	 * Tests the function to parse a DPA response "OS Info"
	 */
	public function testParseInfo(): void {
		Assert::equal($this->expected['osInfo'], $this->parser->parse($this->packet['osInfo']));
	}

	/**
	 * Tests the function to parse a DPA response "HWP configuration"
	 */
	public function testParseHwp(): void {
		Assert::equal($this->expected['hwpConfiguration'], $this->parser->parse($this->packet['hwpConfiguration']));
	}

	/**
	 * Tests the function to parse a response to DPA OS - "Read info" request
	 */
	public function testParseReadInfo(): void {
		Assert::equal($this->expected['osInfo'], $this->parser->parseReadInfo($this->packet['osInfo']));
		$failPacket = preg_replace('/\.24\./', '.ff.', $this->packet['osInfo']);
		$failExpected = $this->expected['osInfo'];
		$failExpected['TrType'] = $failExpected['McuType'] = 'UNKNOWN';
		Assert::equal($failExpected, $this->parser->parseReadInfo($failPacket));
	}

	/**
	 * Tests the function to get RF band from HWP configuration
	 */
	public function testGetRfBand(): void {
		Assert::equal('868 MHz', $this->parser->getRfBand('30'));
		Assert::equal('916 MHz', $this->parser->getRfBand('31'));
		Assert::equal('433 MHz', $this->parser->getRfBand('32'));
	}

	/**
	 * Tests the function to parse response to DPA OS - "Read HWP configuration" request
	 */
	public function testParseHwpConfiguration(): void {
		$actual = $this->parser->parseHwpConfiguration($this->packet['hwpConfiguration']);
		Assert::same($this->expected['hwpConfiguration'], $actual);
	}

}

$test = new OsParserTest();
$test->run();
