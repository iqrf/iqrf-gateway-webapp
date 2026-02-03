<?php

/**
 * Copyright 2017-2026 IQRF Tech s.r.o.
 * Copyright 2019-2026 MICRORISC s.r.o.
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

namespace App\Models\WebSocket\Messages;

use App\Models\WebSocket\Enums\ProxyMessageType;
use JsonSerializable;
use Nette\Utils\Json;
use stdClass;

/**
 * Proxy message base class
 */
abstract class ProxyMessageBase implements JsonSerializable {

	/**
	 * @var int Message timestamp (unix epoch)
	 */
	protected int $timestamp;

	/**
	 * Constructor
	 * @param ProxyMessageType $type Message type
	 * @param int $timestamp Message timestamp (unix epoch)
	 * @param array<mixed>|stdClass|string|null $payload Payload data
	 */
	public function __construct(
		protected readonly ProxyMessageType $type,
		?int $timestamp,
		protected array|stdClass|string|null $payload = null
	) {
		$this->timestamp = $timestamp ?? time();
	}

	/**
	 * Serializes message into JSON
	 * @return array{
	 *  type: string,
	 *  timestamp: int,
	 *  data?: array<mixed>|stdClass|string
	 * } JSON-serialized message
	 */
	public function jsonSerialize(): array {
		$arr = [
			'type' => $this->type->value,
			'timestamp' => $this->timestamp,
		];
		if ($this->payload !== null) {
			$arr['data'] = $this->payload;
		}
		return $arr;
	}

	/**
	 * Converts message into JSON string
	 * @return string JSON string serialized message
	 */
	public function toJsonString(): string {
		return Json::encode($this);
	}

}
