<?php

/**
 * TEST: App\IqrfNetModule\Requests\ApiRequest
 * @covers App\IqrfNetModule\Requests\ApiRequest
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\IqrfNetModule\Requests;

use App\IqrfNetModule\Models\MessageIdManager;
use App\IqrfNetModule\Requests\ApiRequest;
use Mockery;
use Nette\Utils\Json;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for JSON API request manager
 */
class ApiRequestTest extends TestCase {

	/**
	 * @var mixed[] JSON API request in an array
	 */
	private $array = [
		'mType' => 'mngDaemon_Mode',
		'data' => [
			'req' => ['operMode' => 'service'],
			'returnVerbose' => true,
		],
	];

	/**
	 * @var ApiRequest JSON API Request
	 */
	private $request;

	/**
	 * Start up test environment
	 */
	protected function setUp(): void {
		$msgIdManager = Mockery::mock(MessageIdManager::class);
		$msgIdManager->shouldReceive('generate')->andReturn('1');
		$this->request = new ApiRequest($msgIdManager);
	}

	/**
	 * Test function to set the request
	 */
	public function testSetRequest(): void {
		Assert::noError(function (): void {
			$this->request->setRequest($this->array);
		});
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

$test = new ApiRequestTest();
$test->run();
