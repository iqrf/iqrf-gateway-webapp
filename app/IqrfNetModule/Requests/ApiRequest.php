<?php

/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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

namespace App\IqrfNetModule\Requests;

use App\CoreModule\Exceptions\InvalidJsonException;
use App\IqrfNetModule\Models\MessageIdManager;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use stdClass;

/**
 * JSON API request
 */
class ApiRequest {

	/**
	 * @var array<mixed>|stdClass IQRF JSON API request
	 */
	protected array|stdClass $request;

	/**
	 * Constructor
	 * @param MessageIdManager $msgIdManager Message ID manager
	 */
	public function __construct(
		protected MessageIdManager $msgIdManager,
	) {
	}

	/**
	 * Returns the IQRF JSON API request
	 * @return array<mixed>|stdClass IQRF JSON API request
	 */
	public function get(): array|stdClass {
		return $this->request;
	}

	/**
	 * Sets the IQRF JSON API request
	 * @param mixed $request IQRF JSON API request
	 */
	public function set(mixed $request): void {
		if (!is_array($request) && !($request instanceof stdClass)) {
			throw new InvalidJsonException();
		}
		$this->request = $request;
		$this->addMsgId();
	}

	/**
	 * Converts the IQRF JSON DPA request to JSON string
	 * @param bool $pretty Pretty formatted JSON
	 * @return string JSON string
	 * @throws JsonException
	 */
	public function toJson(bool $pretty = false): string {
		return Json::encode($this->request, pretty: $pretty);
	}

	/**
	 * Adds a message ID to the IQRF JSON API request
	 */
	protected function addMsgId(): void {
		if (is_array($this->request) && !isset($this->request['data']['msgId'])) {
			$this->request['data']['msgId'] = $this->msgIdManager->generate();
		} elseif ($this->request instanceof stdClass && !isset($this->request->data->msgId)) {
			$this->request->data->msgId = $this->msgIdManager->generate();
		}
	}

}
