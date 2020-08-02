<?php

/**
 * TEST: App\IqrfNetModule\Responses\ApiResponse
 * @covers App\IqrfNetModule\Requests\ApiResponse
 * @phpVersion >= 7.2
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\IqrfNetModule\Responses;

use App\IqrfNetModule\Exceptions as IqrfException;
use App\IqrfNetModule\Responses\ApiResponse;
use Nette\Utils\Json;
use stdClass;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for JSON API response manager
 */
final class ApiResponseTest extends TestCase {

	/**
	 * @var stdClass JSON API response in an object
	 */
	private $object;

	/**
	 * @var string JSON API response in a string
	 */
	private $json;

	/**
	 * @var ApiResponse JSON API Response
	 */
	private $response;

	/**
	 * Starts up test environment
	 */
	protected function setUp(): void {
		$this->object = (object) [
			'mType' => 'mngDaemon_Mode',
			'data' => (object) [
				'rsp' => (object) ['operMode' => 'service'],
				'msgId' => '1',
				'status' => 0,
			],
		];
		$this->json = Json::encode($this->object);
		$this->response = new ApiResponse();
	}

	/**
	 * Tests the function to set the request (success)
	 */
	public function testSetOk(): void {
		Assert::noError(function (): void {
			$this->response->set($this->json);
		});
	}

	/**
	 * Tests the function to set the request (user error)
	 */
	public function testCheckStatusUserError(): void {
		Assert::exception(function (): void {
			$array = $this->object;
			$array->data->status = 20;
			$json = Json::encode($array);
			$this->response->set($json);
			$this->response->checkStatus();
		}, IqrfException\UserErrorException::class);
	}

	/**
	 * Tests the function to set the request (timeout)
	 */
	public function testCheckStatusTimeout(): void {
		Assert::exception(function (): void {
			$array = $this->object;
			$array->data->status = -1;
			$json = Json::encode($array);
			$this->response->set($json);
			$this->response->checkStatus();
		}, IqrfException\TimeoutException::class);
	}

	/**
	 * Tests the function to get the request
	 */
	public function testGet(): void {
		$this->response->set($this->json);
		Assert::equal($this->object, $this->response->get());
	}

	/**
	 * Tests the function to get the request as JSON string
	 */
	public function testToJson(): void {
		$this->response->set($this->json);
		$expected = Json::encode($this->object, Json::PRETTY);
		Assert::equal($expected, $this->response->toJson(true));
	}

}

$test = new ApiResponseTest();
$test->run();
