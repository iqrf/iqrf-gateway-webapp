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

use App\IqrfAppModule\Exception as IqrfException;
use App\IqrfAppModule\Parser as IqrfParser;
use Nette;
use Nette\Utils\Json;
use Nette\Utils\Strings;

/**
 * Tool for controlling iqrfapp.
 */
class IqrfAppManager {

	use Nette\SmartObject;

	/**
	 * @var WebsocketClient Websocket client
	 */
	private $wsClient;

	/**
	 * @var array DPA parsers
	 */
	private $parsers = [
		IqrfParser\CoordinatorParser::class,
		IqrfParser\EnumerationParser::class,
		IqrfParser\OsParser::class,
	];

	/**
	 * @var array DPA exceptions
	 */
	private $exceptions = [
		-8 => IqrfException\ExclusiveAccessException::class,
		-7 => IqrfException\BadResponseException::class,
		-6 => IqrfException\BadRequestException::class,
		-5 => IqrfException\InterfaceBusyException::class,
		-4 => IqrfException\InterfaceErrorException::class,
		-3 => IqrfException\AbortedException::class,
		-2 => IqrfException\InterfaceQueueFullException::class,
		-1 => IqrfException\TimeoutException::class,
		1 => IqrfException\GeneralFailureException::class,
		2 => IqrfException\IncorrectPcmdException::class,
		3 => IqrfException\IncorrectPnumException::class,
		4 => IqrfException\IncorrectAddressException::class,
		5 => IqrfException\IncorrectDataLengthException::class,
		6 => IqrfException\IncorrectDataException::class,
		7 => IqrfException\IncorrectHwpidUsedException::class,
		8 => IqrfException\IncorrectNadrException::class,
		9 => IqrfException\CustomHandlerConsumedInterfaceDataException::class,
		10 => IqrfException\MissingCustomDpaHandlerException::class,
	];

	/**
	 * Constructor
	 * @param WebsocketClient $wsClient Websocket client
	 */
	public function __construct(WebsocketClient $wsClient) {
		$this->wsClient = $wsClient;
	}

	/**
	 * Send RAW IQRF packet
	 * @param string $packet RAW IQRF packet
	 * @param int $timeout DPA timeout in milliseconds
	 * @return array DPA request and response
	 */
	public function sendRaw(string $packet, int $timeout = null): array {
		$this->fixPacket($packet);
		$array = [
			'mType' => 'iqrfRaw',
			'data' => [
				'timeout' => (int) $timeout,
				'req' => [
					'rData' => $packet,
				],
				'returnVerbose' => true,
			],
		];
		if (!isset($timeout)) {
			unset($array['data']['timeout']);
		}
		$data = $this->wsClient->sendSync($array);
		foreach ($data as &$json) {
			$json = Json::encode($json, Json::PRETTY);
		}
		return $data;
	}

	/**
	 * Change iqrf-daemon operation mode
	 * @param string $mode iqrf-daemon operation mode
	 * @return string Response
	 * @throws IqrfException\InvalidOperationModeException
	 */
	public function changeOperationMode(string $mode) {
		$modes = ['forwarding', 'operational', 'service'];
		if (!in_array($mode, $modes, true)) {
			throw new IqrfException\InvalidOperationModeException();
		}
		$array = [
			'mType' => 'mngDaemon_Mode',
			'data' => [
				'req' => [
					'operMode' => $mode,
				],
				'returnVerbose' => true,
			],
		];
		return Json::encode($this->wsClient->sendSync($array)['response']);
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
	 * @param string $nadr New NADR
	 */
	public function updateNadr(string &$packet, string $nadr): void {
		$this->fixPacket($packet);
		$data = explode('.', $packet);
		$data[0] = Strings::padLeft($nadr, 2, '0');
		$packet = Strings::lower(implode('.', $data));
	}

	/**
	 * Fix DPA packet
	 * @param string $packet DPA packet to fix
	 */
	public function fixPacket(string &$packet): void {
		$data = explode('.', trim($packet, '.'));
		$nadrLo = $data[0];
		$nadrHi = $data[1];
		if ($nadrHi !== '00' && $nadrLo === '00') {
			$data[1] = $nadrLo;
			$data[0] = $nadrHi;
		}
		$packet = Strings::lower(implode('.', $data));
	}

	/**
	 * Parse DPA response
	 * @param array $json JSON DPA response
	 * @return array|null Parsed response in array
	 * @throws IqrfException\EmptyResponseException
	 */
	public function parseResponse(array $json): ?array {
		$this->checkStatus($json);
		$packet = $this->getPacket($json, 'response');
		if (array_key_exists('request', $json)) {
			$nadr = explode('.', $this->getPacket($json, 'request'))[0];
			if ($packet !== '' && $nadr === 'ff') {
				return null;
			}
		}
		if ($packet === '') {
			throw new IqrfException\EmptyResponseException();
		}
		foreach ($this->parsers as $parser) {
			$parsedData = (new $parser)->parse($packet);
			if (isset($parsedData)) {
				return $parsedData;
			}
		}
		return null;
	}

	/**
	 * Chack status from JSON DPA response
	 * @param array $json JSON DPA request and response
	 * @throws IqrfException\UserErrorException
	 */
	public function checkStatus(array $json): void {
		$status = Json::decode($json['response'], Json::FORCE_ARRAY)['data']['status'];
		if ($status === 0) {
			return;
		}
		if (array_key_exists($status, $this->exceptions)) {
			throw new $this->exceptions[$status];
		} else {
			throw new IqrfException\UserErrorException();
		}
	}

	/**
	 * Get a DPA packet from JSON DPA request and reponse
	 * @param array $json JSON DPA request and response
	 * @param string $type Data type (request|response)
	 * @return string DPA packet
	 */
	public function getPacket(array $json, string $type): string {
		$array = Json::decode($json[$type], Json::FORCE_ARRAY);
		$param = $type === 'request' ? 'req' : 'rsp';
		return Strings::lower($array['data'][$param]['rData']);
	}

}
