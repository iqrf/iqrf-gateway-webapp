<?php

/**
 * TEST: App\IqrfNetModule\Requests\DpaRequest
 * @covers App\IqrfNetModule\Requests\DpaRequest
 * @phpVersion >= 7.2
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\IqrfNetModule\Requests;

use App\IqrfNetModule\Models\MessageIdManager;
use App\IqrfNetModule\Requests\DpaRequest;
use Mockery;
use Nette\Utils\Json;
use stdClass;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for JSON DPA request manager
 */
final class DpaRequestTest extends TestCase {

	/**
	 * @var stdClass JSON DPA request in an object
	 */
	private $object;

	/**
	 * @var DpaRequest JSON DPA Request
	 */
	private $request;

	/**
	 * Starts up test environment
	 */
	protected function setUp(): void {
		$msgIdManager = Mockery::mock(MessageIdManager::class);
		$msgIdManager->shouldReceive('generate')->andReturn('1');
		$this->request = new DpaRequest($msgIdManager);
		$this->object = (object) [
			'mType' => 'iqrfRaw',
			'data' => (object) [
				'req' => (object) [
					'rData' => '00.00.06.03.ff.ff',
				],
				'returnVerbose' => true,
				'msgId' => '1',
			],
		];
	}

	/**
	 * Tests the function to set the request (without DPA request fixing)
	 */
	public function testSet(): void {
		$this->request->set($this->object);
		$expected = $this->object;
		$expected->data->msgId = '1';
		Assert::equal($expected, $this->request->get());
	}

	/**
	 * Tests the function to set the request (with DPA Raw packet fixing)
	 */
	public function testSetRawFix(): void {
		$array = $this->object;
		$array->data->req->rData = '00.01.06.03.ff.ff';
		$this->request->set($array);
		$expected = $this->object;
		$expected->data->req->rData = '01.00.06.03.ff.ff';
		$expected->data->msgId = '1';
		Assert::equal($expected, $this->request->get());
	}

	/**
	 * Tests the function to get the request as array
	 */
	public function testGet(): void {
		$this->request->set($this->object);
		$expected = $this->object;
		$expected->data->msgId = '1';
		Assert::equal($expected, $this->request->get());
	}

	/**
	 * Tests the function to get the request as JSON string
	 */
	public function testToJson(): void {
		$this->request->set($this->object);
		$array = $this->object;
		$array->data->msgId = '1';
		$expected = Json::encode($array, Json::PRETTY);
		Assert::equal($expected, $this->request->toJson(true));
	}

}

$test = new DpaRequestTest();
$test->run();
