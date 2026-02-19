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

namespace App\Models\WebSocket\Enums;

/**
 * Proxy message type enum
 */
enum ProxyMessageType: string {

	/**
	 * Proxy authentication failed
	 */
	case PROXY_AUTH_FAILED = 'proxy_auth_failed';
	/**
	 * Proxy authentication success
	 */
	case PROXY_AUTH_SUCCESS = 'proxy_auth_success';
	/**
	 * Proxy message invalid
	 */
	case PROXY_MESSAGE_INVALID = 'proxy_message_invalid';
	/**
	 * Proxy session expired
	 */
	case PROXY_SESSION_EXPIRED = 'proxy_session_expired';
	/**
	 * Proxy session refresh
	 */
	case PROXY_SESSION_REFRESH = 'proxy_session_refresh';
	/**
	 * Proxy session refresh failed
	 */
	case PROXY_SESSION_REFRESH_FAILED = 'proxy_session_refresh_failed';
	/**
	 * Proxy session refresh success
	 */
	case PROXY_SESSION_REFRESH_SUCCESS = 'proxy_session_refresh_success';
	/**
	 * Upstream connection lost
	 */
	case DISCONNECTED = 'upstream_disconnected';
	/**
	 * Upstream authentication failed
	 */
	case UPSTREAM_AUTH_FAILED = 'upstream_auth_failed';
	/**
	 * Reconnecting to upstream
	 */
	case RECONNECTING = 'upstream_reconnecting';
	/**
	 * Upstream ready for messages
	 */
	case READY = 'upstream_ready';
	/**
	 * Request for upstream could not be handled
	 */
	case REQUEST_FAILED = 'upstream_request_failed';
	/**
	 * Invalid request for upstream
	 */
	case REQUEST_INVALID = 'upstream_request_invalid';
	/**
	 * Response from upstream
	 */
	case RESPONSE = 'upstream_response';

}
