<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2018 IQRF Tech s.r.o.
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

namespace App\IqrfNetModule\Models;

use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Exceptions\UserErrorException;
use App\IqrfNetModule\Requests\ApiRequest;
use App\IqrfNetModule\Responses\ApiResponse;
use Nette\SmartObject;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Ratchet\Client;
use Ratchet\Client\WebSocket as WsClient;
use Ratchet\RFC6455\Messaging\MessageInterface;
use React\EventLoop;
use React\Promise\PromiseInterface;
use React\Socket as ReactSocket;
use Tracy\Debugger;

/**
 * WebSocket client
 */
class WebSocketClient {

	use SmartObject;

	/**
	 * @var EventLoop\LoopInterface Event loop
	 */
	private $loop;

	/**
	 * @var string URL to IQRF Gateway Daemon's WebSocket server
	 */
	private $wsServer;

	/**
	 * Constructor
	 * @param string $wsServer URL to IQRF Gateway Daemon's WebSocket server
	 */
	public function __construct(string $wsServer) {
		$this->loop = EventLoop\Factory::create();
		$wsServerEnv = getenv('IQRFGD_WS_SERVER');
		if ($wsServerEnv !== false) {
			$this->wsServer = $wsServerEnv;
		} else {
			$this->wsServer = $wsServer;
		}
	}

	/**
	 * Send IQRF JSON DPA request
	 * @param ApiRequest $request IQRF JSON DPA request
	 * @param int $timeout WebSocket client timeout
	 * @return mixed[] IQRF JSON DPA response
	 * @throws DpaErrorException
	 * @throws EmptyResponseException
	 * @throws JsonException
	 * @throws UserErrorException
	 */
	public function sendSync(ApiRequest $request, int $timeout = 13): array {
		$connection = $this->createConnection($timeout);
		$wait = true;
		$attempts = 2;
		$this->loop->addTimer($timeout * 2, function () use (&$wait): void {
			$this->stopSync($wait);
		});
		$resolved = null;
		$connection->then(function (WsClient $conn) use (&$resolved, &$wait, &$attempts, $request): void {
			$conn->send($request->toJson());
			$conn->on('message', function (MessageInterface $msg) use (&$resolved, &$wait, &$attempts, $conn, $request): void {
				$this->receiveSync($conn, $msg, $resolved, $wait, $attempts, $request);
			});
		}, function ($e) use (&$wait): void {
			Debugger::log($e->getMessage(), 'WebSocket Client');
			$this->stopSync($wait);
		});
		while ($wait) {
			$this->loop->run();
		}
		return $this->parseResponse($request, $resolved);
	}

	/**
	 * Create a connection to WebSocket server
	 * @param int $timeout WebSocket client timeout
	 * @return PromiseInterface React promise
	 */
	private function createConnection(int $timeout): PromiseInterface {
		$reactConnector = new ReactSocket\Connector($this->loop, ['timeout' => $timeout]);
		$connector = new Client\Connector($this->loop, $reactConnector);
		return $connector($this->wsServer);
	}

	/**
	 * Stop event loop
	 * @param bool $wait Wait to finish
	 */
	private function stopSync(bool &$wait): void {
		$wait = false;
		$this->loop->stop();
	}

	/**
	 * Receive a message from WebSocket server
	 * @param Client\WebSocket $connection WebSocket client connection
	 * @param MessageInterface $message Received message
	 * @param MessageInterface|null $resolved Stored receive message
	 * @param bool $wait Wait to finish
	 * @param int $attempts Attempts to receive
	 * @param ApiRequest $request IQRF JSON DPA request
	 * @throws JsonException
	 */
	private function receiveSync(WsClient $connection, MessageInterface $message, ?MessageInterface &$resolved, bool &$wait, int &$attempts, ApiRequest $request): void {
		if ($attempts === 0) {
			$wait = false;
		}
		if ($this->checkMsgId($request, $message)) {
			$resolved = $message;
			$wait = false;
		} else {
			$attempts--;
		}
		$connection->close();
		$this->loop->stop();
	}

	/**
	 * Check if JSON DPA request and response have got the same message ID
	 * @param ApiRequest $request JSON DPA request
	 * @param MessageInterface $response JSON DPA request
	 * @return bool Have JSON DPA request and response got the same message ID
	 * @throws JsonException
	 */
	private function checkMsgId(ApiRequest $request, MessageInterface $response): bool {
		$requestJson = Json::decode($request->toJson(), Json::FORCE_ARRAY);
		$responseJson = Json::decode(strval($response), Json::FORCE_ARRAY);
		return $requestJson['data']['msgId'] === $responseJson['data']['msgId'];
	}

	/**
	 * Parse JSON DPA request and response
	 * @param ApiRequest $request JSON DPA request
	 * @param MessageInterface|null $response JSON DPA response
	 * @return mixed[] JSON DPA response in an array
	 * @throws EmptyResponseException
	 * @throws JsonException
	 * @throws DpaErrorException
	 * @throws UserErrorException
	 */
	private function parseResponse(ApiRequest $request, ?MessageInterface $response): array {
		$string = strval($response);
		$data = ['request' => $request->toArray()];
		if ($string === '') {
			$data['status'] = 'Empty response.';
			Debugger::barDump($data, 'WebSocket client');
			throw new EmptyResponseException();
		}
		$apiResponse = new ApiResponse();
		$apiResponse->setResponse($string);
		$data['response'] = $apiResponse->toArray();
		Debugger::barDump($data, 'WebSocket client');
		return $data;
	}

}
