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
	 * @var WsClient WebSocket client
	 */
	private $client;

	/**
	 * @var EventLoop\LoopInterface Event loop
	 */
	private $loop;

	/**
	 * @var ApiRequest IQRF JSON DPA request
	 */
	private $request;

	/**
	 * @var MessageInterface|null Response from WebSocket server
	 */
	private $response;

	/**
	 * @var string URL to IQRF Gateway Daemon's WebSocket server
	 */
	private $serverUrl;

	/**
	 * @var bool Wait to finish
	 */
	private $wait = true;

	/**
	 * Constructor
	 * @param string $serverUrl URL to IQRF Gateway Daemon's WebSocket server
	 */
	public function __construct(string $serverUrl) {
		$this->loop = EventLoop\Factory::create();
		$serverUrlEnv = getenv('IQRFGD_WS_SERVER');
		if ($serverUrlEnv !== false) {
			$this->serverUrl = $serverUrlEnv;
		} else {
			$this->serverUrl = $serverUrl;
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
		$this->request = $request;
		$this->loop->addTimer($timeout * 2, [$this, 'stopSync']);
		$connection->then(function (WsClient $conn): void {
			$this->client = $conn;
			$this->client->send($this->request->toJson());
			$this->client->on('message', [$this, 'receiveSync']);
		}, function ($e): void {
			Debugger::log($e->getMessage(), 'WebSocket Client');
			$this->stopSync();
		});
		while ($this->wait) {
			$this->loop->run();
		}
		return $this->parseResponse();
	}

	/**
	 * Create a connection to WebSocket server
	 * @param int $timeout WebSocket client timeout
	 * @return PromiseInterface React promise
	 */
	private function createConnection(int $timeout): PromiseInterface {
		$reactConnector = new ReactSocket\Connector($this->loop, ['timeout' => $timeout]);
		$connector = new Client\Connector($this->loop, $reactConnector);
		return $connector($this->serverUrl);
	}

	/**
	 * Stop event loop
	 */
	private function stopSync(): void {
		$this->wait = false;
		$this->loop->stop();
	}

	/**
	 * Receive a message from WebSocket server
	 * @param MessageInterface $message Received message
	 */
	public function receiveSync(MessageInterface $message): void {
		$this->response = $message;
		$this->client->close();
		$this->stopSync();
	}

	/**
	 * Parse JSON DPA request and response
	 * @return mixed[] JSON DPA response in an array
	 * @throws EmptyResponseException
	 * @throws JsonException
	 * @throws DpaErrorException
	 * @throws UserErrorException
	 */
	private function parseResponse(): array {
		$data = ['request' => $this->request->toArray()];
		if ($this->response === null) {
			$data['status'] = 'Empty response.';
			Debugger::barDump($data, 'WebSocket client');
			throw new EmptyResponseException();
		}
		$string = $this->response->getPayload();
		$apiResponse = new ApiResponse();
		$apiResponse->setResponse($string);
		$data['response'] = $apiResponse->toArray();
		Debugger::barDump($data, 'WebSocket client');
		return $data;
	}

}
