<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017 IQRF Tech s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace App\IqrfAppModule\Model;

use App\IqrfAppModule\Model\CoordinatorParser;
use App\IqrfAppModule\Model\OsParser;
use App\Model\CommandManager;
use Nette;
use Nette\Utils\Json;

class IqrfAppManager {

	use Nette\SmartObject;

	/**
	 * @var CommandManager
	 */
	private $commandManager;

	/**
	 * @var CoordinatorParser
	 */
	private $coordinatorParser;

	/**
	 * @var OsParser
	 */
	private $osParser;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command Manager
	 * @param CoordinatorParser $coordinatorParser
	 * @param OsParser $osParser
	 */
	public function __construct(CommandManager $commandManager, CoordinatorParser $coordinatorParser, OsParser $osParser) {
		$this->commandManager = $commandManager;
		$this->coordinatorParser = $coordinatorParser;
		$this->osParser = $osParser;
	}

	/**
	 * Send JSON request to iqrfapp
	 * @param array $array JSON request on array
	 * @return string JSON response
	 */
	public function sendCommand($array) {
		$cmd = 'iqrfapp "' . str_replace('"', '\\"', Json::encode($array)) . '"';
		return $this->commandManager->send($cmd, true);
	}

	/**
	 * Send RAW IQRF packet
	 * @param string $packet RAW IQRF packet
	 * @param int $timeout DPA timeout in milliseconds
	 * @return string DPA response
	 */
	public function sendRaw($packet, $timeout = null) {
		$array = [
			'ctype' => 'dpa',
			'type' => 'raw',
			'msgid' => '1',
			'timeout' => (int) $timeout,
			'request' => $packet,
			'request_ts' => '',
			'confirmation' => '',
			'confirmation_ts' => '',
			'response' => '',
			'response_ts' => '',
		];
		if (empty($timeout)) {
			unset($array['timeout']);
		}
		$data = [
			'request' => Json::encode($array, Json::PRETTY),
			'response' => str_replace('Received: ', '', $this->sendCommand($array)),
		];
		return $data;
	}

	/**
	 * Change iqrf-daemon operation mode
	 * @param string $mode iqrf-daemon operation mode
	 * @return string Response
	 */
	public function changeOperationMode($mode) {
		$modes = ['forwarding', 'operational', 'service'];
		if (!in_array($mode, $modes, true)) {
			return null;
			// throw new \Exception();
		}
		$array = [
			'ctype' => 'conf',
			'type' => 'mode',
			'cmd' => $mode,
		];
		return $this->sendCommand($array);
	}

	/**
	 * Validate raw IQRF packet
	 * @param string $packet Raw IQRF packet
	 * @return bool Status
	 */
	public function validatePacket($packet) {
		$pattern = '/^([0-9a-fA-F]{1,2}(\.|\ )){1,64}[0-9a-fA-F]{1,2}(\.|)$/';
		return (bool) preg_match($pattern, $packet);
	}

	/**
	 * Parse DPA response
	 * @param string $json JSON DPA response
	 * @return array Parsed response in array
	 * @throws Exception
	 */
	public function parseResponse($json) {
		$jsonResponse = $json['response'];
		if (empty($jsonResponse) || $jsonResponse === 'Timeout') {
			return null;
			// throw new \Exception();
		}
		$response = Json::decode($jsonResponse, Json::FORCE_ARRAY);
		$status = $response['status'];
		if ($status !== 'STATUS_NO_ERROR') {
			return null;
			// throw new \Exception();
		}
		$packet = $response['response'];
		if (empty($packet)) {
			return null;
			// throw new \Exception();
		}
		$pnum = explode('.', $packet)[2];
		switch ($pnum) {
			case '00':
				return $this->coordinatorParser->parse($packet);
			case '02':
				return $this->osParser->parse($packet);
			default:
				return null;
		}
	}

}
