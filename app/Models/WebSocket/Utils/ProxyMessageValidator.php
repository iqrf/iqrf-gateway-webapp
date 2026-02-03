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

namespace App\Models\WebSocket\Utils;

use stdClass;

/**
 * Utility class for validating proxy server / session messages
 */
final class ProxyMessageValidator {

	/**
	 * Message type indicating successful authentication of upstream session
	 */
	private const AUTH_SUCCESS_MESSAGE_TYPE = 'auth_success';

	/**
	 * Message type indicating authentication error
	 */
	private const AUTH_FAILED_MESSAGE_TYPE = 'auth_failed';

	/**
	 * Constructor is made private to prevent instantiation
	 */
	private function __construct() {
	}

	/**
	 * Checks if JSON object is an authentication success message.
	 *
	 * The authentication success message is expected to have message type
	 * `auth_success`, an integer property `expiration` and a bool property `service`.
	 *
	 * The expiration is represented by unix epoch in seconds.
	 * Service property indicates whether session can use service mode.
	 *
	 * @param stdClass $json JSON object of a messsage
	 * @return bool `true` if message is authentication success, `false` otherwise
	 */
	public static function isAuthSuccessMessage(stdClass $json): bool {
		return (property_exists($json, 'type') && $json->type === self::AUTH_SUCCESS_MESSAGE_TYPE) &&
			(property_exists($json, 'expiration') && is_int($json->expiration)) &&
			(property_exists($json, 'service') && is_bool($json->service));
	}

	/**
	 * Checks if JSON object is an authentication error message.
	 *
	 * The authentication error message is expected to have message type
	 * `auth_failed`, an integer property `code` and a string property `error`.
	 *
	 * The code represents authentication failure code with a simple
	 * descriptor of the error is contained in the error property.
	 *
	 * @param stdClass $json JSON object of a messsage
	 * @return bool `true` if message is authentication error, `false` otherwise
	 */
	public static function isAuthErrorMessage(stdClass $json): bool {
		return (property_exists($json, 'type') && $json->type === self::AUTH_FAILED_MESSAGE_TYPE) &&
			(property_exists($json, 'code') && is_int($json->code)) &&
			(property_exists($json, 'error') && is_string($json->error));
	}

	/**
	 * Checks if JSON object is a Daemon API message.
	 *
	 * This method only checks for presence of message type property,
	 * data object and message ID property.
	 *
	 * @param stdClass $json JSON object of a message
	 * @return bool `true` if message is Daemon API message, `false` otherwise
	 */
	public static function isDaemonApiMessage(stdClass $json): bool {
		return (property_exists($json, 'mType') && is_string($json->mType)) &&
			(property_exists($json, 'data') && is_object($json->data)) &&
			(property_exists($json->data, 'msgId') && is_string($json->data->msgId));
	}

}
