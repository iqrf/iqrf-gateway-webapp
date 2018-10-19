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
use Nette\DI\Container;
use Nette\Utils\Json;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for JSON API response manager
 */
class ApiResponseTest extends TestCase {

	/**
	 * @var array JSON API response in an array
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
	 * @var Container Nette Tester Container
	 */
	private $container;

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
	 * @param Container $container Nette Tester Container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
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
		Assert::noError(function () {
			$this->response->setResponse($this->json);
		});
	}

	/**
	 * Test function to set the request (user error)
	 */
	public function testSetRequestUserError(): void {
		Assert::exception(function () {
			$array = $this->array;
			$array['data']['status'] = 20;
			$json = Json::encode($array);
			$this->response->setResponse($json);
		}, IqrfException\UserErrorException::class);
	}

	/**
	 * Test function to set the request (timeout)
	 */
	public function testSetRequestTimeout(): void {
		Assert::exception(function () {
			$array = $this->array;
			$array['data']['status'] = -1;
			$json = Json::encode($array);
			$this->response->setResponse($json);
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


$test = new ApiResponseTest($container);
$test->run();
