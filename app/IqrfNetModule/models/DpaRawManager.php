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

use App\IqrfNetModule\Exceptions as IqrfException;
use App\IqrfNetModule\Parsers as IqrfParser;
use App\IqrfNetModule\Requests\DpaRequest;
use Nette\SmartObject;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;

/**
 * Tool for sending and parsing JSON DPA Raw requests and responses
 */
class DpaRawManager {

	use SmartObject;

	/**
	 * @var DpaRequest JSON DPA request
	 */
	private $request;

	/**
	 * @var WebSocketClient WebSocket client
	 */
	private $wsClient;

	/**
	 * @var string[] DPA parsers
	 */
	private $parsers = [
		IqrfParser\CoordinatorParser::class,
		IqrfParser\EnumerationParser::class,
		IqrfParser\OsParser::class,
	];

	/**
	 * Constructor
	 * @param DpaRequest $request JSON DPA request
	 * @param WebSocketClient $wsClient WebSocket client
	 */
	public function __construct(DpaRequest $request, WebSocketClient $wsClient) {
		$this->request = $request;
		$this->wsClient = $wsClient;
	}

	/**
	 * Send RAW IQRF packet
	 * @param string $packet RAW IQRF packet
	 * @param int|null $timeout DPA timeout in milliseconds
	 * @return mixed[] DPA request and response
	 * @throws IqrfException\EmptyResponseException
	 * @throws JsonException
	 */
	public function send(string $packet, ?int $timeout = null): array {
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
		$this->request->setRequest($array);
		$data = $this->wsClient->sendSync($this->request);
		foreach ($data as &$json) {
			$json = Json::encode($json, Json::PRETTY);
		}
		return $data;
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
		$data = explode('.', $packet);
		$data[0] = Strings::padLeft($nadr, 2, '0');
		$data[1] = '00';
		$packet = Strings::lower(implode('.', $data));
	}

	/**
	 * Parse DPA response
	 * @param mixed[] $json JSON DPA response
	 * @return mixed[]|null Parsed response in array
	 * @throws IqrfException\EmptyResponseException
	 * @throws JsonException
	 */
	public function parseResponse(array $json): ?array {
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
			$parsedData = (new $parser())->parse($packet);
			if (isset($parsedData)) {
				return $parsedData;
			}
		}
		return null;
	}

	/**
	 * Get a DPA packet from JSON DPA request and response
	 * @param mixed[] $json JSON DPA request and response
	 * @param string $type Data type (request|response)
	 * @return string DPA packet
	 * @throws JsonException
	 */
	public function getPacket(array $json, string $type): string {
		$array = Json::decode($json[$type], Json::FORCE_ARRAY);
		$param = $type === 'request' ? 'req' : 'rsp';
		return Strings::lower($array['data'][$param]['rData']);
	}

}
