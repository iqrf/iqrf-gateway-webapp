<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2019 IQRF Tech s.r.o.
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

use App\IqrfNetModule\Enums\UploadFormats;
use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Exceptions\UserErrorException;
use App\IqrfNetModule\Requests\ApiRequest;
use Nette\Utils\JsonException;

/**
 * Upload manager
 */
class UploadManager {

	/**
	 * @var ApiRequest IQRF Gateway Daemon's JSON API request
	 */
	private $apiRequest;

	/**
	 * @var WebSocketClient WebSocket client
	 */
	private $wsClient;

	/**
	 * Constructor
	 * @param ApiRequest $apiRequest IQRF Gateway Daemon's JSON API request
	 * @param WebSocketClient $wsClient WebSocket client
	 */
	public function __construct(ApiRequest $apiRequest, WebSocketClient $wsClient) {
		$this->apiRequest = $apiRequest;
		$this->wsClient = $wsClient;
	}

	/**
	 * Uploads a file into the coordinator
	 * @param string $filePath Path to the file to upload
	 * @param UploadFormats|null $format File format
	 * @return mixed[] API request and response
	 * @throws DpaErrorException
	 * @throws EmptyResponseException
	 * @throws JsonException
	 * @throws UserErrorException
	 */
	public function upload(string $filePath, ?UploadFormats $format = null): array {
		$request = [
			'mType' => 'mngDaemon_Upload',
			'data' => [
				'req' => [
					'fileName' => $filePath,
				],
			],
		];
		if ($format !== null) {
			$request['data']['req']['target'] = $format->toScalar();
		}
		$this->apiRequest->setRequest($request);
		return $this->wsClient->sendSync($this->apiRequest);
	}

}
