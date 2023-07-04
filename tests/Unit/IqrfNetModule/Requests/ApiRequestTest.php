<?php

/**
 * TEST: App\IqrfNetModule\Requests\ApiRequest
 * @covers App\IqrfNetModule\Requests\ApiRequest
 * @phpVersion >= 7.4
 * @testCase
 */
/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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
final class ApiRequestTest extends TestCase {

	/**
	 * @var array<string, array<string, bool|array<string, string>>|string> IQRF JSON API request in an array
	 */
	private const REQUEST = [
		'mType' => 'mngDaemon_Mode',
		'data' => [
			'req' => ['operMode' => 'service'],
			'returnVerbose' => true,
		],
	];

	/**
	 * @var ApiRequest JSON API Request
	 */
	private ApiRequest $request;

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
	public function testSetValid(): void {
		Assert::noError(function (): void {
			$this->request->set(self::REQUEST);
		});
	}

	/**
	 * Tests the function to set the request (invalid request)
	 */
	public function testSetInvalid(): void {
		Assert::exception(function (): void {
			$this->request->set(null);
		}, InvalidJsonException::class);
	}

	/**
	 * Tests the function to get the request as array
	 */
	public function testGet(): void {
		$this->request->set(self::REQUEST);
		$expected = self::REQUEST;
		$expected['data']['msgId'] = '1';
		Assert::equal($expected, $this->request->get());
	}

	/**
	 * Tests the function to get the request as JSON string
	 */
	public function testToJson(): void {
		$this->request->set(self::REQUEST);
		$array = self::REQUEST;
		$array['data']['msgId'] = '1';
		$expected = Json::encode($array, pretty: true);
		Assert::equal($expected, $this->request->toJson(true));
	}

}

$test = new ApiRequestTest();
$test->run();
