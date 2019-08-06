<?php

/**
 * TEST: App\IqrfNetModule\Requests\ApiRequest
 * @covers App\IqrfNetModule\Requests\ApiRequest
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\IqrfNetModule\Requests;

use App\CoreModule\Exceptions\InvalidJsonException;
use App\IqrfNetModule\Models\MessageIdManager;
use App\IqrfNetModule\Requests\ApiRequest;
use Mockery;
use Nette\Utils\Json;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for JSON API request manager
 */
class ApiRequestTest extends TestCase {

	/**
	 * @var mixed[] IQRF JSON API request in an array
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
	 * Starts up test environment
	 */
	protected function setUp(): void {
		$msgIdManager = Mockery::mock(MessageIdManager::class);
		$msgIdManager->shouldReceive('generate')->andReturn('1');
		$this->request = new ApiRequest($msgIdManager);
	}

	/**
	 * Tests the function to set the request (valid request)
	 */
	public function testSetRequestValid(): void {
		Assert::noError(function (): void {
			$this->request->setRequest($this->array);
		});
	}

	/**
	 * Tests the function to set the request (invalid request)
	 */
	public function testSetRequestInvalid(): void {
		Assert::exception(function (): void {
			$this->request->setRequest(null);
		}, InvalidJsonException::class);
	}

	/**
	 * Tests the function to get the request as array
	 */
	public function testToArray(): void {
		$this->request->setRequest($this->array);
		$expected = $this->array;
		$expected['data']['msgId'] = '1';
		Assert::equal($expected, $this->request->toArray());
	}

	/**
	 * Tests the function to get the request as JSON string
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
