<?php

/**
 * TEST: App\IqrfNetModule\Responses\ApiResponse
 * @covers App\IqrfNetModule\Requests\ApiResponse
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\IqrfNetModule\Responses;

use App\IqrfNetModule\Exceptions as IqrfException;
use App\IqrfNetModule\Responses\ApiResponse;
use Nette\Utils\Json;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for JSON API response manager
 */
class ApiResponseTest extends TestCase {

	/**
	 * @var mixed{} JSON API response in an array
	 */
	private $array = [
		'mType' => 'mngDaemon_Mode',
		'data' => [
			'rsp' => ['operMode' => 'service'],
			'msgId' => '1',
			'status' => 0,
		],
	];

	/**
	 * @var string JSON API response in a string
	 */
	private $json;

	/**
	 * @var ApiResponse JSON API Response
	 */
	private $response;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->json = Json::encode($this->array);
	}

	/**
	 * Start up test environment
	 */
	protected function setUp(): void {
		$this->response = new ApiResponse();
	}

	/**
	 * Test function to set the request (success)
	 */
	public function testSetRequestOk(): void {
		Assert::noError(function (): void {
			$this->response->setResponse($this->json);
		});
	}

	/**
	 * Test function to set the request (user error)
	 */
	public function testCheckStatusUserError(): void {
		Assert::exception(function (): void {
			$array = $this->array;
			$array['data']['status'] = 20;
			$json = Json::encode($array);
			$this->response->setResponse($json);
			$this->response->checkStatus();
		}, IqrfException\UserErrorException::class);
	}

	/**
	 * Test function to set the request (timeout)
	 */
	public function testCheckStatusTimeout(): void {
		Assert::exception(function (): void {
			$array = $this->array;
			$array['data']['status'] = -1;
			$json = Json::encode($array);
			$this->response->setResponse($json);
			$this->response->checkStatus();
		}, IqrfException\TimeoutException::class);
	}

	/**
	 * Test function to get the request as array
	 */
	public function testToArray(): void {
		$this->response->setResponse($this->json);
		$expected = $this->array;
		$expected['data']['msgId'] = '1';
		Assert::equal($expected, $this->response->toArray());
	}

	/**
	 * Test function to get the request as JSON string
	 */
	public function testToJson(): void {
		$this->response->setResponse($this->json);
		$array = $this->array;
		$array['data']['msgId'] = '1';
		$expected = Json::encode($array, Json::PRETTY);
		Assert::equal($expected, $this->response->toJson(true));
	}

}

$test = new ApiResponseTest();
$test->run();
