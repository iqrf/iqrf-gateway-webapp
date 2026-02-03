import { type DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';

export enum UpstreamStatus {
	UNKNOWN,
	DISCONNECTED,
	RECONNECTING,
	READY,
}

export const upstreamStatusI18nKeys: Record<UpstreamStatus, string> = {
	[UpstreamStatus.UNKNOWN]: 'unknown',
	[UpstreamStatus.DISCONNECTED]: 'disconnected',
	[UpstreamStatus.RECONNECTING]: 'reconnecting',
	[UpstreamStatus.READY]: 'ready',
};

export enum ProxyMessageType {
	PROXY_AUTH_FAILED = 'proxy_auth_failed',
	PROXY_SESSION_EXPIRED = 'proxy_session_expired',
	UPSTREAM_DISCONNECTED = 'upstream_disconnected',
	UPSTREAM_AUTH_FAILED = 'upstream_auth_failed',
	UPSTREAM_RECONNECTING = 'upstream_reconnecting',
	UPSTREAM_READY = 'upstream_ready',
	UPSTREAM_REQUEST_FAILED = 'upstream_request_failed',
	UPSTREAM_REQUEST_INVALID = 'upstream_request_invalid',
	UPSTRAEM_RESPONSE = 'upstream_response',
}

export interface ProxyMessage {
	type: ProxyMessageType;
	timestamp: number;
}

export interface UpstreamReconnecting extends ProxyMessage {
	data: {
		attempt: number;
		delay: number;
	};
}

export interface UpstreamResponse extends ProxyMessage {
	type: ProxyMessageType.UPSTRAEM_RESPONSE;
	data: DaemonApiResponse;
}
