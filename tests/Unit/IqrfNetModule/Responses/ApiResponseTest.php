<?php

/**
 * TEST: App\IqrfNetModule\Responses\ApiResponse
 * @covers App\IqrfNetModule\Requests\ApiResponse
 * @phpVersion >= 7.4
 * @testCase
 */
/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
declare(strict_types = 1);

namespace Tests\Unit\IqrfNetModule\Responses;

use App\IqrfNetModule\Exceptions\DpaErrorException;
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
	private stdClass $object;

	/**
	 * @var string JSON API response in a string
	 */
	private string $json;

	/**
	 * @var ApiResponse JSON API Response
	 */
	private ApiResponse $response;

	/**
	 * Tests the function to set the request (success)
	 */
	public function testSetOk(): void {
		Assert::noError(function (): void {
			$this->response->set($this->json);
		});
	}

	/**
	 * Tests the function to set the request (timeout)
	 */
	public function testCheckStatusTimeout(): void {
		Assert::exception(function (): void {
			$array = $this->object;
			$array->data->status = -1;
			$array->data->statusStr = 'Timeout';
			$json = Json::encode($array);
			$this->response->set($json);
			$this->response->checkStatus();
		}, DpaErrorException::class, 'Timeout', -1);
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
		$expected = Json::encode($this->object, pretty: true);
		Assert::equal($expected, $this->response->toJson(true));
	}

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

}

$test = new ApiResponseTest();
$test->run();
