<?php

/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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

namespace App\CoreModule\Models;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use SplObjectStorage;
use Throwable;

/**
 * WebSocket server class
 */
class WebSocketDispatcher implements MessageComponentInterface {

	/**
	 * @var SplObjectStorage Connected clients
	 */
	protected $clients;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->clients = new SplObjectStorage();
	}

	/**
	 * Connection opened callback
	 * @param ConnectionInterface $conn Incoming connection
	 */
	public function onOpen(ConnectionInterface $conn): void {
		$this->clients->attach($conn);
	}

	/**
	 * Message received callback
	 * @param ConnectionInterface $from Sender
	 * @param mixed $msg Message
	 */
	public function onMessage(ConnectionInterface $from, $msg): void {
		foreach ($this->clients as $client) {
			if ($from !== $client) {
				$client->send($msg);
			}
		}
	}

	/**
	 * Connection closed callback
	 * @param ConnectionInterface $conn Closed connection
	 */
	public function onClose(ConnectionInterface $conn): void {
		$this->clients->detach($conn);
	}

	/**
	 * Connection error callback
	 * @param ConnectionInteface $conn Connection where error occured
	 * @param Throwable $e Exception
	 */
	public function onError(ConnectionInterface $conn, Throwable $e): void {
		$conn->close();
	}

}
