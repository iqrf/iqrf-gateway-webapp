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
declare(strict_types=1);

namespace App\IqrfAppModule\Model;

use App\IqrfAppModule\Model\CoordinatorParser;
use App\IqrfAppModule\Model\EmptyResponseException;
use App\IqrfAppModule\Model\InvalidOperationModeException;
use App\IqrfAppModule\Model\OsParser;
use App\Model\CommandManager;
use DateTime;
use Nette;
use Nette\Utils\Json;
use Tracy\Debugger;

/**
 * Tool for controlling iqrfapp.
 */
class IqrfAppManager {

	use Nette\SmartObject;

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * @var CoordinatorParser Parser for DPA Coordinator responses
	 */
	private $coordinatorParser;

	/**
	 * @var OsParser Parser for DPA OS responses
	 */
	private $osParser;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 * @param CoordinatorParser $coordinatorParser Parser for DPA Coordinator responses
	 * @param OsParser $osParser Parser for DPA OS responses
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
	public function sendCommand(array $array) {
		$cmd = 'iqrfapp "' . str_replace('"', '\\"', Json::encode($array)) . '"';
		return $this->commandManager->send($cmd, true);
	}

	/**
	 * Send RAW IQRF packet
	 * @param string $packet RAW IQRF packet
	 * @param int $timeout DPA timeout in milliseconds
	 * @return array DPA request and response
	 */
	public function sendRaw(string $packet, int $timeout = null): array {
		$now = new DateTime();
		$array = [
			'ctype' => 'dpa',
			'type' => 'raw',
			'msgid' => (string) $now->getTimestamp(),
			'timeout' => (int) $timeout,
			'request' => $this->fixPacket($packet),
			'request_ts' => '',
			'confirmation' => '',
			'confirmation_ts' => '',
			'response' => '',
			'response_ts' => '',
		];
		if (!isset($timeout)) {
			unset($array['timeout']);
		}
		// Workaround to fix mismatched msgid
		$this->readOnly(200);
		$commandOutput = $this->sendCommand($array);
		if (empty($commandOutput)) {
			throw new EmptyResponseException();
		}
		preg_match('/Received: {(.*?)\}/s', $commandOutput, $output);
		$response = !empty($output) ? str_replace('Received: ', '', $output[0]) : null;
		$data = [
			'request' => Json::encode($array, Json::PRETTY),
			'response' => $response,
		];
		Debugger::barDump($data, 'iqrfapp');
		return $data;
	}

	/**
	 * Read only (async) DPA packet
	 * @param int $timeout DPA timeout in milliseconds
	 * @return string JSON response
	 */
	public function readOnly(int $timeout = null) {
		$cmd = 'iqrfapp readonly';
		$cmd .= isset($timeout) ? ' timeout ' . $timeout : '';
		return $this->commandManager->send($cmd, true);
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
			'ctype' => 'conf',
			'type' => 'mode',
			'cmd' => $mode,
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
	 * Fix DPA packet
	 * @param string $packet DPA packet to fix
	 * @return string Fixed DPA packet
	 */
	public function fixPacket(string $packet): string {
		$data = explode('.', trim($packet, '.'));
		$nadrLo = $data[1];
		$nadrHi = $data[0];
		if ($nadrHi === '00' && $nadrLo !== '00') {
			$data[0] = $nadrLo;
			$data[1] = $nadrHi;
		}
		return implode('.', $data);
	}

	/**
	 * Parse DPA response
	 * @param string $json JSON DPA response
	 * @return array Parsed response in array
	 * @throws EmptyResponseException
	 */
	public function parseResponse(array $json) {
		$jsonResponse = $json['response'];
		if (empty($jsonResponse) || $jsonResponse === 'Timeout') {
			throw new EmptyResponseException();
		}
		$response = Json::decode($jsonResponse, Json::FORCE_ARRAY);
		$status = $response['status'];
		if ($status !== 'STATUS_NO_ERROR') {
			return null;
			// TODO: throw own exception
		}
		$packet = $response['response'];
		if (empty($packet)) {
			throw new EmptyResponseException();
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
