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

/**
 * Upstream request invalid message
 */
class UpstreamRequestInvalid extends ProxyMessageBase {

	/**
	 * Constructs upstream request invalid
	 * @param string $message Invalid message
	 * @param int|null $timestamp Message timestamp (unix epoch)
	 */
	public function __construct(string $message, ?int $timestamp = null) {
		parent::__construct(
			type: ProxyMessageType::REQUEST_INVALID,
			timestamp: $timestamp,
			payload: $message,
		);
	}

}
