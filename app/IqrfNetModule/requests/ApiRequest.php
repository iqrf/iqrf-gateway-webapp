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

namespace App\IqrfNetModule\Requests;

use App\IqrfNetModule\Model\MessageIdManager;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

/**
 * JSON API request
 */
class ApiRequest {

	/**
	 * @var MessageIdManager Message ID manager
	 */
	protected $msgIdManager;

	/**
	 * @var array JSON API request
	 */
	protected $request;

	/**
	 * Constructor
	 * @param MessageIdManager $msgIdManager Message ID manager
	 */
	public function __construct(MessageIdManager $msgIdManager) {
		$this->msgIdManager = $msgIdManager;
	}

	/**
	 * Add a message ID to the JSON API request
	 */
	protected function addMsgId(): void {
		if (!array_key_exists('msgId', $this->request['data'])) {
			$this->request['data']['msgId'] = $this->msgIdManager->generate();
		}
	}

	/**
	 * Set JSON API request
	 * @param array $request JSON API request
	 */
	public function setRequest(array $request): void {
		$this->request = $request;
		$this->addMsgId();
	}

	/**
	 * Convert JSON API request to a array
	 * @return array JSON API request
	 */
	public function toArray(): array {
		return $this->request;
	}

	/**
	 * Convert JSON DPA request to JSON string
	 * @param bool $pretty Pretty formatted JSON
	 * @return string JSON string
	 * @throws JsonException
	 */
	public function toJson(bool $pretty = false): string {
		$options = $pretty ? Json::PRETTY : 0;
		return Json::encode($this->request, $options);
	}

}
