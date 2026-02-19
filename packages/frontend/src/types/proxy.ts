import { type DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';

/**
 * Upstream session status
 */
export enum UpstreamStatus {
	UNKNOWN,
	DISCONNECTED,
	RECONNECTING,
	READY,
}

/**
 * Mapping of upstream session status values to translation keys
 */
export const upstreamStatusI18nKeys: Record<UpstreamStatus, string> = {
	[UpstreamStatus.UNKNOWN]: 'unknown',
	[UpstreamStatus.DISCONNECTED]: 'disconnected',
	[UpstreamStatus.RECONNECTING]: 'reconnecting',
	[UpstreamStatus.READY]: 'ready',
};

/**
 * Proxy message enum
 */
export enum ProxyMessageType {
	PROXY_AUTH_FAILED = 'proxy_auth_failed',
	PROXY_AUTH_SUCCESS = 'proxy_auth_success',
	PROXY_SESSION_EXPIRED = 'proxy_session_expired',
	PROXY_SESSION_REFRESH = 'proxy_session_refresh',
	UPSTREAM_DISCONNECTED = 'upstream_disconnected',
	UPSTREAM_AUTH_FAILED = 'upstream_auth_failed',
	UPSTREAM_RECONNECTING = 'upstream_reconnecting',
	UPSTREAM_READY = 'upstream_ready',
	UPSTREAM_REQUEST_FAILED = 'upstream_request_failed',
	UPSTREAM_REQUEST_INVALID = 'upstream_request_invalid',
	UPSTRAEM_RESPONSE = 'upstream_response',
}

/**
 * Base proxy message interface
 */
export interface ProxyMessage {
	/**
	 * Message type
	 */
	type: ProxyMessageType;
	/**
	 * Message timestamp
	 */
	timestamp: number;
}

/**
 * Proxy authentication success message interface
 */
export interface ProxyAuthSuccess extends ProxyMessage {
	type: ProxyMessageType.PROXY_AUTH_SUCCESS;
	/**
	 * Message data
	 */
	data: {
		/**
		 * Assigned session ID
		 */
		sessionId: number;
	};
}

/**
 * Proxy session refersh message interface
 */
export interface ProxySessionRefresh extends ProxyMessage {
	type: ProxyMessageType.PROXY_SESSION_REFRESH;
	/**
	 * Message data
	 */
	data: {
		/**
		 * Session ID
		 */
		sessionId: number;
		/**
		 * Access token
		 */
		token: string;
	};
}

/**
 * Upstream session reconnecting message interface
 */
export interface UpstreamReconnecting extends ProxyMessage {
	type: ProxyMessageType.UPSTREAM_RECONNECTING;
	/**
	 * Message data
	 */
	data: {
		/**
		 * Reconnect attempt
		 */
		attempt: number;
		/**
		 * Delay before reconnecting
		 */
		delay: number;
	};
}

/**
 * Upstream response message interface
 */
export interface UpstreamResponse extends ProxyMessage {
	type: ProxyMessageType.UPSTRAEM_RESPONSE;
	/**
	 * Message data - Daemon API respons
	 */
	data: DaemonApiResponse;
}
