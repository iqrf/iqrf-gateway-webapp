<?php

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

namespace App\IqrfNetModule\Models;

use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Requests\ApiRequest;
use App\IqrfNetModule\Responses\ApiResponse;
use InvalidArgumentException;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Ratchet\Client;
use Ratchet\Client\WebSocket as WsClient;
use Ratchet\RFC6455\Messaging\MessageInterface;
use React\Dns\Config\Config as DnsConfig;
use React\EventLoop;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use React\Socket as ReactSocket;
use stdClass;
use Tracy\Debugger;

/**
 * WebSocket client
 */
class WebSocketClient {

	/**
	 * @var LoopInterface Event loop
	 */
	private LoopInterface $loop;

	/**
	 * @var string URL to IQRF Gateway Daemon's WebSocket server
	 */
	private string $serverUrl;

	/**
	 * Constructor
	 * @param string $url URL to IQRF Gateway Daemon's WebSocket server
	 */
	public function __construct(string $url) {
		$this->loop = EventLoop\Loop::get();
		$envUrl = getenv('IQRFGD_WS_SERVER');
		$this->serverUrl = ($envUrl !== false) ? $envUrl : $url;
	}

	/**
	 * Sends IQRF JSON API request
	 * @param ApiRequest $request IQRF JSON API request
	 * @param bool $checkStatus Check response status
	 * @param int $timeout WebSocket client timeout
	 * @return array{request: array<mixed>|stdClass, response: stdClass} IQRF JSON API response
	 * @throws DpaErrorException
	 * @throws EmptyResponseException
	 */
	public function sendSync(ApiRequest $request, bool $checkStatus = true, int $timeout = 13): array {
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
		return $this->parseResponse($request, $resolved, $checkStatus);
	}

	/**
	 * Creates a connection to WebSocket server
	 * @param int $timeout WebSocket client timeout
	 * @return PromiseInterface<WsClient> React promise
	 */
	private function createConnection(int $timeout): PromiseInterface {
		$options = ['timeout' => $timeout];
		$dnsConfig = DnsConfig::loadSystemConfigBlocking();
		$options['dns'] = false;
		$reactConnector = new ReactSocket\Connector($this->loop, $options);
		foreach ($dnsConfig->nameservers as $nameserver) {
			try {
				$options['dns'] = $nameserver;
				$reactConnector = new ReactSocket\Connector($this->loop, $options);
				break;
			} catch (InvalidArgumentException $e) {
				continue;
			}
		}
		$connector = new Client\Connector($this->loop, $reactConnector);
		return $connector($this->serverUrl);
	}

	/**
	 * Stops event loop
	 * @param bool $wait Wait to finish
	 */
	private function stopSync(bool &$wait): void {
		$wait = false;
		$this->loop->stop();
	}

	/**
	 * Receives a message from WebSocket server
	 * @param Client\WebSocket $connection WebSocket client connection
	 * @param MessageInterface $message Received message
	 * @param MessageInterface|null $resolved Stored receive message
	 * @param bool $wait Wait to finish
	 * @param int $attempts Attempts to receive
	 * @param ApiRequest $request IQRF JSON API request
	 * @throws JsonException
	 */
	private function receiveSync(WsClient $connection, MessageInterface $message, ?MessageInterface &$resolved, bool &$wait, int &$attempts, ApiRequest $request): void {
		if ($attempts === 0) {
			$wait = false;
		}
		if ($this->checkMessage($request, $message)) {
			$resolved = $message;
			$wait = false;
		} else {
			$attempts--;
		}
		$connection->close();
		$this->loop->stop();
	}

	/**
	 * Checks if JSON DPA request and response have got the same message ID
	 * @param ApiRequest $request JSON DPA request
	 * @param MessageInterface $response JSON DPA request
	 * @return bool Have JSON DPA request and response got the same message ID
	 * @throws JsonException
	 */
	private function checkMessage(ApiRequest $request, MessageInterface $response): bool {
		$requestJson = Json::decode($request->toJson());
		$responseJson = Json::decode($response->getPayload());
		$correctMsqId = $requestJson->data->msgId === $responseJson->data->msgId;
		$correctMType = $requestJson->mType === $responseJson->mType;
		return $correctMsqId && $correctMType;
	}

	/**
	 * Parses IQRF JSON API request and response
	 * @param ApiRequest $request JSON DPA request
	 * @param MessageInterface|null $response IQRF JSON API response
	 * @param bool $checkStatus Check response status
	 * @return array{request: array<mixed>|stdClass, response: stdClass} IQRF JSON API response in an array
	 * @throws EmptyResponseException
	 * @throws DpaErrorException
	 */
	private function parseResponse(ApiRequest $request, ?MessageInterface $response, bool $checkStatus): array {
		$data = ['request' => $request->get()];
		if ($response === null) {
			$data['status'] = 'Empty response.';
			Debugger::barDump($data, 'WebSocket client');
			throw new EmptyResponseException();
		}
		$apiResponse = new ApiResponse();
		$apiResponse->set($response->getPayload());
		$data['response'] = $apiResponse->get();
		Debugger::barDump($data, 'WebSocket client');
		if ($checkStatus) {
			$apiResponse->checkStatus();
		}
		return $data;
	}

}
