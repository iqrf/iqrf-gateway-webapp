<?php

/**
 * TEST: App\IqrfNetModule\Models\DpaRawManager
 * @covers App\IqrfNetModule\Models\DpaRawManager
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Test\Unit\IqrfNetModule\Models;

use App\CoreModule\Models\FileManager;
use App\CoreModule\Models\JsonFileManager;
use App\IqrfNetModule\Models\DpaRawManager;
use App\IqrfNetModule\Requests\DpaRequest;
use Mockery;
use Tester\Assert;
use Tests\Toolkit\TestCases\WebSocketTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for DPA Raw request and response manager
 */
class DpaRawManagerTest extends WebSocketTestCase {

	/**
	 * @var DpaRawManager DPA Raw request and response manager
	 */
	private $manager;

	/**
	 * @var FileManager Text file manager
	 */
	private $fileManager;

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $jsonFileManager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$path = __DIR__ . '/../../../data/iqrf/';
		$this->fileManager = new FileManager($path);
		$this->jsonFileManager = new JsonFileManager($path);
		$this->request = Mockery::mock(DpaRequest::class);
		$this->manager = new DpaRawManager($this->request, $this->wsClient);
	}

	/**
	 * Tests the function to send a DPA packet
	 */
	public function testSend(): void {
		$packet = '01.00.06.03.ff.ff';
		$request = [
			'mType' => 'iqrfRaw',
			'data' => [
				'req' => [
					'rData' => $packet,
				],
				'returnVerbose' => true,
			],
		];
		$this->assertRequest($request, function () use ($packet): void {
			$this->manager->send($packet);
		});
	}

	/**
	 * Tests the function to a validation of DPA packet (invalid packet)
	 */
	public function testValidatePacketInvalid(): void {
		$packets = [
			'01 00 06 03 ff ff',
			'01 00 06 03 ff ff.',
			';01.00.06.03.ff.ff',
			';01 00 06 03 ff ff',
			'01.00.06.03.ff.ff;',
			'01 00 06 03 ff ff;',
			'; echo Test > test.log',
		];
		foreach ($packets as $packet) {
			Assert::false($this->manager->validatePacket($packet));
		}
	}

	/**
	 * Tests the function to a validation of DPA packet (valid packet)
	 */
	public function testValidatePacketValid(): void {
		$packets = [
			'01.00.06.03.ff.ff',
			'01.00.06.03.ff.ff.',
		];
		foreach ($packets as $packet) {
			Assert::true($this->manager->validatePacket($packet));
		}
	}

	/**
	 * Tests the function to update a network address in the DPA packet
	 */
	public function testUpdateNadr(): void {
		$packet = '01.00.06.03.ff.ff';
		$nadr = 'F';
		$expected = '0f.00.06.03.ff.ff';
		$this->manager->updateNadr($packet, $nadr);
		Assert::same($expected, $packet);
	}


	/**
	 * Tests the function to parse a broadcast DPA response
	 */
	public function testParseResponseBroadcast(): void {
		$array = [
			'request' => $this->fileManager->read('request-broadcast.json'),
			'response' => $this->fileManager->read('response-broadcast.json'),
		];
		Assert::null($this->manager->parseResponse($array));
	}

	/**
	 * Tests the function to parse DPA OS response
	 */
	public function testParseResponseOsParser(): void {
		$array = ['response' => $this->fileManager->read('response-os-read.json')];
		$expected = $this->jsonFileManager->read('data-os-read');
		$actual = $this->manager->parseResponse($array);
		Assert::equal($expected, $actual);
	}

	/**
	 * Tests the function to parse DPA response (without parser)
	 */
	public function testParseResponseWithoutParser(): void {
		$array = ['response' => $this->fileManager->read('response-ledr-on.json')];
		Assert::null($this->manager->parseResponse($array));
	}

	/**
	 * Tests the function to get a DPA packet from the JSON DPA request
	 */
	public function testGetPacketRequest(): void {
		$expected = '00.00.02.00.ff.ff';
		$array = ['request' => $this->fileManager->read('request-os-read.json')];
		$actual = $this->manager->getPacket($array, 'request');
		Assert::same($expected, $actual);
	}

	/**
	 * Tests the function to get a DPA packet from the JSON DPA response
	 */
	public function testGetPacketResponse(): void {
		$expected = '00.00.02.80.00.00.00.00.dc.3c.10.81.42.24.b8.08.00.28.00.31';
		$array = ['response' => $this->fileManager->read('response-os-read.json')];
		$actual = $this->manager->getPacket($array, 'response');
		Assert::same($expected, $actual);
	}

}

$test = new DpaRawManagerTest();
$test->run();
