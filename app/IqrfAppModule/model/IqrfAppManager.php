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

use App\IqrfAppModule\Model\CoordinatorParser;
use App\IqrfAppModule\Model\EmptyResponseException;
use App\IqrfAppModule\Model\EnumerationParser;
use App\IqrfAppModule\Model\InvalidOperationModeException;
use App\IqrfAppModule\Model\MessageIdManager;
use App\IqrfAppModule\Model\OsParser;
use Nette;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Ratchet\Client as WebSocketClient;
use Ratchet\RFC6455\Messaging as WebSocketMessaging;
use React\EventLoop;
use React\Socket as ReactSocket;
use Tracy\Debugger;

/**
 * Tool for controlling iqrfapp.
 */
class IqrfAppManager {

	use Nette\SmartObject;

	/**
	 * @var CoordinatorParser Parser for DPA Coordinator responses
	 */
	private $coordinatorParser;

	/**
	 * @var EnumerationParser Parser for DPA Enumeration responses
	 */
	private $enumParser;

	/**
	 * @var MessageIdManager Message ID manager
	 */
	private $msgIdManager;

	/**
	 * @var OsParser Parser for DPA OS responses
	 */
	private $osParser;

	/**
	 * @var string URL to IQRF Gateway Daemon's WebSocket server
	 */
	private $wsServer;

	/**
	 * @var array DPA parsers
	 */
	private $parsers = [
		CoordinatorParser::class,
		EnumerationParser::class,
		OsParser::class,
	];

	/**
	 * Constructor
	 * @param string $wsServer URL to IQRF Gateway Daemon's WebSocket server
	 * @param MessageIdManager $msgIdManager Message ID manager
	 */
	public function __construct(string $wsServer, MessageIdManager $msgIdManager) {
		$this->msgIdManager = $msgIdManager;
		$this->wsServer = $wsServer;
	}

	/**
	 * Send JSON request to IQRF Gateway Daemon via WebSocket
	 * @param array $array JSON request in array
	 * @return string JSON response
	 */
	public function sendCommand(array $array) {
		$loop = EventLoop\Factory::create();
		$reactConnector = new ReactSocket\Connector($loop, ['timeout' => 15]);
		$connector = new WebSocketClient\Connector($loop, $reactConnector);
		$connection = $connector($this->wsServer);
		$wait = true;
		$attempts = 2;
		$loop->addTimer(25.0, function () use ($loop, &$wait) {
			$wait = false;
			$loop->stop();
		});
		$resolved = null;
		$connection->then(function (WebSocketClient\WebSocket $conn) use (&$resolved, &$wait, &$attempts, $loop, $array) {
			$conn->send(Json::encode($array));
			$conn->on('message', function (WebSocketMessaging\MessageInterface $msg) use (&$resolved, &$wait, &$attempts, $loop, $conn, $array) {
				$json = Json::decode((string) $msg, Json::FORCE_ARRAY);
				$correctMsgId = $array['data']['msgId'] === $json['data']['msgId'];
				if ($correctMsgId) {
					$resolved = $msg;
					$wait = false;
				} else if (!$correctMsgId && $attempts > 0) {
					$attempts--;
				} else {
					$wait = false;
				}
				$conn->close();
				$loop->stop();
			});
		}, function ($e) use (&$wait, $loop) {
			Debugger::log($e->getMessage(), 'websocket');
			$wait = false;
			$loop->stop();
		});
		while ($wait) {
			$loop->run();
		}
		return (string) $resolved;
	}

	/**
	 * Send RAW IQRF packet
	 * @param string $packet RAW IQRF packet
	 * @param int $timeout DPA timeout in milliseconds
	 * @return array DPA request and response
	 */
	public function sendRaw(string $packet, int $timeout = null): array {
		$array = [
			'mType' => 'iqrfRaw',
			'data' => [
				'msgId' => $this->msgIdManager->generate(),
				'timeout' => (int) $timeout,
				'req' => [
					'rData' => $this->fixPacket($packet),
				],
			],
			'returnVerbose' => true,
		];
		if (!isset($timeout)) {
			unset($array['data']['timeout']);
		}
		$response = $this->sendCommand($array);
		if (empty($response)) {
			throw new EmptyResponseException();
		}
		$data = [
			'request' => Json::encode($array, Json::PRETTY),
			'response' => $response,
		];
		Debugger::barDump($data, 'iqrfapp');
		return $data;
	}

	/**
	 * Change iqrf-daemon operation mode
	 * @param string $mode iqrf-daemon operation mode
	 * @return string Response
	 * @throws InvalidOperationModeException
	 */
	public function changeOperationMode(string $mode) {
		$modes = ['forwarding', 'operational', 'service'];
		if (!in_array($mode, $modes, true)) {
			throw new InvalidOperationModeException();
		}
		$array = [
			'mType' => 'mngDaemon_Mode',
			'data' => [
				'msgId' => $this->msgIdManager->generate(),
				'req' => [
					'operMode' => $mode,
				],
			],
			'returnVerbose' => true,
		];
		return $this->sendCommand($array);
	}

	/**
	 * Validate DPA packet
	 * @param string $packet DPA packet to validate
	 * @return bool Status
	 */
	public function validatePacket(string $packet): bool {
		$pattern = '/^([0-9a-fA-F]{1,2}\.){4,62}[0-9a-fA-F]{1,2}(\.|)$/';
		return (bool) preg_match($pattern, $packet);
	}

	/**
	 * Update NADR in DPA packet
	 * @param string $packet DPA packet to modify
	 * @param string $nadr NADR
	 * @return string Modified DPA packet
	 */
	public function updateNadr(string $packet, string $nadr): string {
		$data = explode('.', $this->fixPacket($packet));
		$data[0] = Strings::padLeft($nadr, 2, '0');
		return Strings::lower(implode('.', $data));
	}

	/**
	 * Fix DPA packet
	 * @param string $packet DPA packet to fix
	 * @return string Fixed DPA packet
	 */
	public function fixPacket(string $packet): string {
		$data = explode('.', trim($packet, '.'));
		$nadrLo = $data[0];
		$nadrHi = $data[1];
		if ($nadrHi !== '00' && $nadrLo === '00') {
			$data[1] = $nadrLo;
			$data[0] = $nadrHi;
		}
		return Strings::lower(implode('.', $data));
	}

	/**
	 * Parse DPA response
	 * @param array $json JSON DPA response
	 * @return array|null Parsed response in array
	 * @throws EmptyResponseException
	 */
	public function parseResponse(array $json) {
		$jsonResponse = $json['response'];
		if (empty($jsonResponse)) {
			throw new EmptyResponseException();
		}
		$response = Json::decode($jsonResponse, Json::FORCE_ARRAY)['data'];
		$status = $response['status'];
		if ($status !== 0) {
			return null;
			/** @todo throw own exception */
		}
		$packet = Strings::lower($response['rsp']['rData']);
		if (array_key_exists('request', $json)) {
			$request = Json::decode($json['request'], Json::FORCE_ARRAY);
			$requestPacket = Strings::lower($request['data']['req']['rData']);
			$requestNadr = explode('.', $requestPacket)[0];
			Debugger::barDump($requestPacket);
			Debugger::barDump($requestNadr);
			if (empty($packet) && $requestNadr === 'ff') {
				return null;
			}
		}
		if (empty($packet)) {
			throw new EmptyResponseException();
		}
		foreach ($this->parsers as $parser) {
			$parsedData = (new $parser)->parse($packet);
			if ($parsedData !== null) {
				return $parsedData;
			}
		}
	}

}
