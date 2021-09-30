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

use InvalidArgumentException;
use Ratchet\Client\Connector as RatchetConnector;
use Ratchet\Client\WebSocket as RatchetWsClient;
use React\Dns\Config\Config as DnsConfig;
use React\EventLoop;
use React\Promise\PromiseInterface;
use React\Socket\Connector as ReactConnector;

/**
 * Generic WebSocket client
 */
class WebSocketClient {

	/**
	 * @var EventLoop\LoopInterface Event loop
	 */
	private $loop;

	/**
	 * @var string WebSocket server URL
	 */
	private $url;

	public function __construct(string $url) {
		$this->loop = EventLoop\Loop::get();
		$this->url = $url;
	}

	public function createConnection(int $timeout): PromiseInterface {
		$options = [
			'dns' => false,
			'timeout' => $timeout,
		];
		$dnsConfig = DnsConfig::loadSystemConfigBlocking();
		$reactConnector = new ReactConnector($this->loop, $options);
		foreach ($dnsConfig->nameservers as $nameserver) {
			try {
				$options['dns'] = $nameserver;
				$reactConnector = new ReactConnector($this->loop, $options);
				break;
			} catch (InvalidArgumentException $e) {
				continue;
			}
		}
		$connector = new RatchetConnector($this->loop, $reactConnector);
		return $connector($this->url);
	}

	public function send(string $message): void {
		$connection = $this->createConnection(10);
		$connection->then(function (RatchetWsClient $con) use (&$message): void {
			$con->send($message);
		}, function (\Exception $e) {
			$f = fopen('php://output', 'w');
			fputs($f, 'Failed to connect: ' . $e->getMessage() . PHP_EOL);
			fclose($f);
		});
	}

}
