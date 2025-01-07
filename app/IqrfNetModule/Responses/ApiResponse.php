<?php

/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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

namespace App\IqrfNetModule\Responses;

use App\IqrfNetModule\Exceptions\DpaErrorException;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use stdClass;

/**
 * IQRF JSON API request
 */
class ApiResponse {

	/**
	 * @var stdClass JSON API response
	 */
	protected stdClass $response;

	/**
	 * Checks a status from the IQRF JSON API response
	 * @throws DpaErrorException
	 */
	public function checkStatus(): void {
		$status = $this->response->data->status;
		if ($status === 0) {
			return;
		}
		throw new DpaErrorException($this->response->data->statusStr ?? '', $status);
	}

	/**
	 * Returns the IQRF JSON API response
	 * @return stdClass IQRF JSON API response
	 */
	public function get(): stdClass {
		return $this->response;
	}

	/**
	 * Sets the IQRF JSON API response
	 * @param string $response IQRF JSON API response
	 */
	public function set(string $response): void {
		try {
			$this->response = Json::decode($response);
		} catch (JsonException) {
			$this->response = (object) [];
		}
	}

	/**
	 * Converts IQRF JSON API request to a JSON string
	 * @param bool $pretty Pretty formatted JSON
	 * @return string JSON string
	 * @throws JsonException
	 */
	public function toJson(bool $pretty = false): string {
		return Json::encode($this->response, pretty: $pretty);
	}

}
