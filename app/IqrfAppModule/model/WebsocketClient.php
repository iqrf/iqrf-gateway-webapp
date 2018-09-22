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

namespace App\IqrfAppModule\Model;

use App\IqrfAppModule\Exception\EmptyResponseException;
use App\IqrfAppModule\Model\MessageIdManager;
use Nette;
use Nette\Utils\Json;
use Ratchet\Client;
use Ratchet\Client\WebSocket as WsClient;
use Ratchet\RFC6455\Messaging\MessageInterface;
use React\EventLoop;
use React\Promise\PromiseInterface;
use React\Socket as ReactSocket;
use Tracy\Debugger;

/**
 * Websocket client
 */
class WebsocketClient {

	use Nette\SmartObject;

	/**
	 * @var MessageIdManager Message ID manager
	 */
	private $msgIdManager;

	/**
	 * @var EventLoop\LoopInterface Event loop
	 */
	private $loop;

	/**
	 * @var string URL to IQRF Gateway Daemon's Websocket server
	 */
	private $wsServer;

	/**
	 * Constructor
	 * @param string $wsServer URL to IQRF Gateway Daemon's Websocket server
	 * @param MessageIdManager $msgIdManager Message ID manager
	 */
	public function __construct(string $wsServer, MessageIdManager $msgIdManager) {
		$this->loop = EventLoop\Factory::create();
		$this->msgIdManager = $msgIdManager;
		$this->wsServer = $wsServer;
	}

	/**
	 * Add a message ID to the IQRF JSON DPA request
	 * @param array $request IQRF JSON DPA request
	 */
	private function addMsgId(array &$request): void {
		if (!array_key_exists('msgId', $request['data'])) {
			$request['data']['msgId'] = $this->msgIdManager->generate();
		}
	}

	/**
	 * Send IQRF JSON DPA request
	 * @param array $request IQRF JSON DPA request
	 * @param int $timeout Websocket client timeout
	 * @return array IQRF JSON DPA response
	 */
	public function sendSync(array $request, int $timeout = 15): array {
		$connection = $this->createConnection($timeout);
		$wait = true;
		$attempts = 2;
		$this->loop->addTimer($timeout * 2, function () use (&$wait) {
			$this->stopSync($wait);
		});
		$resolved = null;
		$this->addMsgId($request);
		$connection->then(function (WsClient $conn) use (&$resolved, &$wait, &$attempts, $request) {
			$conn->send(Json::encode($request));
			$conn->on('message', function (MessageInterface $msg) use (&$resolved, &$wait, &$attempts, $conn, $request) {
				$this->receiveSync($conn, $msg, $resolved, $wait, $attempts, $request);
			});
		}, function ($e) use (&$wait) {
			Debugger::log($e->getMessage(), 'websocket');
			$this->stopSync($wait);
		});
		while ($wait) {
			$this->loop->run();
		}
		return $this->parseResponse($request, $resolved);
	}

	/**
	 * Check if JSON DPA request and response have got the same message ID
	 * @param array $request JSON DPA request
	 * @param MessageInterface $response JSON DPA request
	 * @return bool Have JSON DPA request and response got the same message ID
	 */
	private function checkMsgId(array $request, MessageInterface $response): bool {
		$json = Json::decode(strval($response), Json::FORCE_ARRAY);
		return $request['data']['msgId'] === $json['data']['msgId'];
	}

	/**
	 * Create a connection to websocket server
	 * @param int $timeout Websocket client timeout
	 * @return PromiseInterface React promise
	 */
	private function createConnection(int $timeout): PromiseInterface {
		$reactConnector = new ReactSocket\Connector($this->loop, ['timeout' => $timeout]);
		$connector = new Client\Connector($this->loop, $reactConnector);
		return $connector($this->wsServer);
	}

	/**
	 * Parse JSON DPA request and response
	 * @param array $request JSON DPA request
	 * @param MessageInterface $response JSON DPA response
	 * @return array JSON DPA response in an array
	 * @throws EmptyResponseException
	 */
	private function parseResponse(array $request, ?MessageInterface $response): array {
		$string = strval($response);
		$data = ['request' => $request];
		if ($string === '') {
			$data['status'] = 'Empty response.';
			Debugger::barDump($data, 'Websocket client');
			throw new EmptyResponseException();
		}
		$data['response'] = Json::decode($string, Json::FORCE_ARRAY);
		Debugger::barDump($data, 'Websocket client');
		return $data;
	}

	/**
	 * Receive a message from websocket server
	 * @param Client\WebSocket $connection Websocket client connection
	 * @param MessageInterface $message Received message
	 * @param MessageInterface|null $resolved Stored receive message
	 * @param bool $wait Wait to finish
	 * @param int $attempts Attempts to receive
	 * @param array $request IQRF JSON DPA request
	 */
	private function receiveSync(WsClient $connection, MessageInterface $message, ?MessageInterface &$resolved, bool &$wait, int &$attempts, array $request): void {
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
	 * Stop event loop
	 * @param bool $wait Wait to finish
	 */
	private function stopSync(bool &$wait): void {
		$wait = false;
		$this->loop->stop();
	}

}
