<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2019 IQRF Tech s.r.o.
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

namespace Tests\Toolkit\TestCases;

use App\IqrfNetModule\Models\WebSocketClient;
use App\IqrfNetModule\Requests\ApiRequest;
use Mockery;
use Mockery\MockInterface;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Tester\Assert;
use Tester\TestCase;

/**
 * WebSocket Test case
 */
abstract class WebSocketTestCase extends TestCase {

	/**
	 * @var ApiRequest|MockInterface Mocked IQRF JSON API request
	 */
	protected $request;

	/**
	 * @var WebSocketClient|MockInterface Mocked WebSocket client
	 */
	protected $wsClient;

	/**
	 * Asserts the IQRF JSON API request
	 * @param mixed[] $request IQRF JSON API request
	 * @param callable $callback Callback
	 */
	protected function assertRequest(array $request, callable $callback): void {
		$this->request->shouldReceive('setRequest')->with($request);
		$this->wsClient->shouldReceive('sendSync')->with(Mockery::type(ApiRequest::class));
		Assert::noError($callback);
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->request = Mockery::mock(ApiRequest::class);
		$this->wsClient = Mockery::mock(WebSocketClient::class);
	}

	/**
	 * Cleanups the test environment
	 */
	protected function tearDown(): void {
		Mockery::close();
	}

	/**
	 * Reads the IQRF JSON API response
	 * @param string $mType Message type
	 * @return mixed[] IQRF JSON API response
	 */
	public function readJsonResponse(string $mType): array {
		$path = __DIR__ . '/../../data/apiResponses/';
		$file = FileSystem::read($path . $mType . '.json');
		$response['response'] = Json::decode($file, Json::FORCE_ARRAY);
		return $response;
	}

}
