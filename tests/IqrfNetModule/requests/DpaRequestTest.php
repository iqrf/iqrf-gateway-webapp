<?php

/**
 * TEST: App\IqrfNetModule\Requests\DpaRequest
 * @covers App\IqrfNetModule\Requests\DpaRequest
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\IqrfNetModule\Requests;

use App\IqrfNetModule\Models\MessageIdManager;
use App\IqrfNetModule\Requests\DpaRequest;
use Mockery;
use Nette\Utils\Json;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for JSON DPA request manager
 */
class DpaRequestTest extends TestCase {

	/**
	 * @var mixed[] JSON DPA request in an array
	 */
	private $array = [
		'mType' => 'iqrfRaw',
		'data' => [
			'req' => [
				'rData' => '00.00.06.03.ff.ff',
			],
			'returnVerbose' => true,
			'msgId' => '1',
		],
	];

	/**
	 * @var DpaRequest JSON DPA Request
	 */
	private $request;

	/**
	 * Start up test environment
	 */
	protected function setUp(): void {
		$msgIdManager = Mockery::mock(MessageIdManager::class);
		$msgIdManager->shouldReceive('generate')->andReturn('1');
		$this->request = new DpaRequest($msgIdManager);
	}

	/**
	 * Test function to set the request (without DPA request fixing)
	 */
	public function testSetRequest(): void {
		$this->request->setRequest($this->array);
		$expected = $this->array;
		$expected['data']['msgId'] = '1';
		Assert::equal($expected, $this->request->toArray());
	}

	/**
	 * Test function to set the request (with DPA Raw packet fixing)
	 */
	public function testSetRequestRawFix(): void {
		$array = $this->array;
		$array['data']['req']['rData'] = '00.01.06.03.ff.ff';
		$this->request->setRequest($array);
		$expected = $this->array;
		$expected['data']['req']['rData'] = '01.00.06.03.ff.ff';
		$expected['data']['msgId'] = '1';
		Assert::equal($expected, $this->request->toArray());
	}

	/**
	 * Test function to get the request as array
	 */
	public function testToArray(): void {
		$this->request->setRequest($this->array);
		$expected = $this->array;
		$expected['data']['msgId'] = '1';
		Assert::equal($expected, $this->request->toArray());
	}

	/**
	 * Test function to get the request as JSON string
	 */
	public function testToJson(): void {
		$this->request->setRequest($this->array);
		$array = $this->array;
		$array['data']['msgId'] = '1';
		$expected = Json::encode($array, Json::PRETTY);
		Assert::equal($expected, $this->request->toJson(true));
	}

}

$test = new DpaRequestTest();
$test->run();
