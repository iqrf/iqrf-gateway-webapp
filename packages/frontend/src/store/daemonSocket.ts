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

import { DaemonMode } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { type DaemonApiRequest, type DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { DaemonMessage, type DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { defineStore } from 'pinia';
import { v4 as uuidv4 } from 'uuid';
import { toast } from 'vue3-toastify';

import { DaemonApiSendError } from '@/errors/DaemonApiSendError';
import UrlBuilder from '@/helpers/urlBuilder';
import { waitUntil } from '@/helpers/wait';
import ClientSocket, { type GenericSocketState } from '@/modules/clientSocket';
import i18n from '@/plugins/i18n';
import { useMonitorStore } from '@/store/monitorSocket';
import {
	type ProxyAuthSuccess,
	type ProxyMessage,
	ProxyMessageType,
	type ProxySessionRefresh,
	type UpstreamReconnecting,
	type UpstreamResponse,
	UpstreamStatus,
} from '@/types/proxy';


interface UpstreamState {
	status: UpstreamStatus;
	nextAttempt: number|null;
}

interface DaemonState extends GenericSocketState {
	sessionId: number|null;
	receivedMessages: number;
	messages: DaemonMessage[];
	version: string;
	versionMsgId: string;
	upstream: UpstreamState;
}

const serviceModeWhitelist: string[] = [
	'mngDaemon_Exit',
	'mngDaemon_Mode',
	'mngDaemon_Version',
	'mngDaemon_Upload',
	'cfgDaemon_Component',
	'mngScheduler_AddTask',
	'mngScheduler_GetTask',
	'mngScheduler_List',
	'mngScheduler_RemoveAll',
	'mngScheduler_RemoveTask',
];

export const useDaemonStore = defineStore('daemon', {
	state: (): DaemonState => ({
		socket: null,
		connected: false,
		reconnecting: false,
		reconnected: false,
		sessionId: null,
		receivedMessages: 0,
		messages: [],
		version: '',
		versionMsgId: '',
		upstream: {
			status: UpstreamStatus.UNKNOWN,
			nextAttempt: null,
		},
	}),
	actions: {
		/**
		 * Initializes websocket
		 * @param {string} jwt Authentication token
		 */
		initSocket(jwt: string): void {
			if (this.socket !== null) {
				return;
			}
			const urlBuilder = new UrlBuilder();
			this.socket = new ClientSocket(
				{
					url: `${urlBuilder.getWebSocketProxyUrl()}?token=${jwt}`,
					autoConnect: true,
					reconnect: true,
					reconnectDelay: 5_000,
				},
				this.onOpen,
				this.onClose,
				this.onError,
				this.onProxyMessage,
			);
		},
		/**
		 * Attempts to refresh proxy server session
		 * @param {string} jwt New access token
		 */
		refreshSession(jwt: string): void {
			if (this.socket === null) {
				console.error(
					i18n.global.t('components.status.daemonApi.messages.proxySessionRefreshNoSocket'),
				);
				return;
			}
			if (this.sessionId === null) {
				console.error(
					i18n.global.t('components.status.daemonApi.messages.proxySessionRefreshNoSessionId'),
				);
				return;
			}
			const message: ProxySessionRefresh = {
				type: ProxyMessageType.PROXY_SESSION_REFRESH,
				timestamp: Math.floor(Date.now() / 1_000),
				data: {
					sessionId: this.sessionId,
					token: jwt,
				},
			};
			this.socket.sendProxyMessage(message);
		},
		/**
		 * Closes and destroys socket if it exists
		 */
		destroySocket(): void {
			if (this.socket !== null) {
				this.socket.close();
				this.socket = null;
			}
			this.sessionId = null;
			this.reconnecting = false;
		},
		/**
		 * On socket open action (used as callback)
		 */
		onOpen(): void {
			if (this.reconnecting) {
				setTimeout(() => {
					this.reconnecting = false;
					this.connected = true;
				}, 1_000);
			} else {
				this.connected = true;
			}
		},
		/**
		 * On WebSocket message handler (used as callback)
		 * This method is passed as handler for incoming messages from WebSocket proxy server.
		 * Messages pertaining to upstream state are handled here, forwarded responses from Daemon API
		 * server are passed to the `onMessage` handler for purposes of backwards compatibility of components
		 * listening for actions with $onAction and `onMessage`.
		 * @param {MessageEvent<string>} event Message event
		 */
		onProxyMessage(event: MessageEvent<string>): void {
			const message: ProxyMessage = JSON.parse(event.data) as ProxyMessage;
			if (message.type === ProxyMessageType.PROXY_AUTH_FAILED) {
				console.error(
					i18n.global.t('components.status.daemonApi.messages.proxyAuthFailed'),
				);
				return;
			}
			if (message.type === ProxyMessageType.PROXY_AUTH_SUCCESS) {
				const data = (message as ProxyAuthSuccess).data;
				this.sessionId = data.sessionId;
				return;
			}
			if (message.type === ProxyMessageType.UPSTREAM_AUTH_FAILED) {
				console.error(
					i18n.global.t('components.status.daemonApi.messages.upstreamAuthFailed'),
				);
				return;
			}
			if (message.type === ProxyMessageType.UPSTREAM_DISCONNECTED) {
				this.upstream.status = UpstreamStatus.DISCONNECTED;
				console.error(
					i18n.global.t('components.status.daemonApi.messages.upstreamDisconnected'),
				);
				return;
			}
			if (message.type === ProxyMessageType.UPSTREAM_RECONNECTING) {
				const data = (message as UpstreamReconnecting).data;
				this.upstream.status = UpstreamStatus.RECONNECTING;
				this.upstream.nextAttempt = (message.timestamp + data.delay) * 1_000;
				return;
			}
			if (message.type === ProxyMessageType.UPSTREAM_READY) {
				this.upstream.status = UpstreamStatus.READY;
				this.upstream.nextAttempt = null;
				return;
			}
			if (message.type === ProxyMessageType.UPSTRAEM_RESPONSE) {
				this.onMessage((message as UpstreamResponse).data);
			}
		},
		/**
		 * Daemon API message handler.
		 * If the incoming message is a response to version request
		 * the version is first parsed and stored before passing the request further for processing.
		 * Responses are stored for matching with requests.
		 * @param {DaemonApiResponse} message Daemon API response
		 * @return {DaemonApiResponse} IQRF Gateway Daemon API response
		 */
		onMessage(message: DaemonApiResponse): DaemonApiResponse {
			if (message.mType === 'mngDaemon_Version' && message.data.msgId === this.versionMsgId) {
				const tokens = new RegExp(/v\d+\.\d+\.\d+/).exec(message.data.rsp.version as string);
				if (tokens !== null && tokens.length > 0) {
					this.version = tokens[0];
				}
			}
			return message;
		},
		/**
		 * On socket close action (used as callback)
		 * @param {CloseEvent} event Close event
		 */
		onClose(event: CloseEvent): void {
			console.error(event);
			this.connected = false;
			this.sessionId = null;
			this.upstream.status = UpstreamStatus.UNKNOWN;
			if (this.socket !== null) {
				this.reconnecting = true;
			}
		},
		/**
		 * On socket error action (used as callback)
		 * @param {Event} event Error event
		 */
		onError(event: Event): void {
			console.error(event);
		},
		/**
		 * Send a request to fetch Daemon version
		 */
		sendVersionRequest(): void {
			if (!this.socket) {
				return;
			}
			this.versionMsgId = uuidv4();
			const request: DaemonApiRequest = {
				mType: 'mngDaemon_Version',
				data: {
					msgId: this.versionMsgId,
					returnVerbose: true,
				},
			};
			this.socket.send(request);
		},
		/**
		 * Sends a message via proxy server to Daemon API for processing.
		 * If the message options do not contain a request, an error is thrown.
		 * Similarly, the socket is not initialized or upstream is not ready, an error is thrown.
		 * If the request does not contain a message ID for matching with response,
		 * UUIDv4 is generated for it and stored before sending the message.
		 * The message is not sent until Daemon is done with network enumeration or reading sensor data.
		 * @param {DaemonMessageOptions} options Daemon API message options
		 * @return {Promise<string>} Message ID
		 */
		async sendMessage(options: DaemonMessageOptions): Promise<string> {
			const message = options.request;
			if (message === null) {
				throw new DaemonApiSendError(
					i18n.global.t('common.messages.noDaemonMessage'),
				);
			}
			if (!this.socket || this.upstream.status !== UpstreamStatus.READY) {
				throw new DaemonApiSendError(
					i18n.global.t('common.messages.proxyUnavailable', { mType: message.mType }),
				);
			}
			const monitorStore = useMonitorStore();
			if (monitorStore.mode === DaemonMode.Service && !serviceModeWhitelist.includes(message.mType)) {
				throw new DaemonApiSendError(
					i18n.global.t('common.messages.serviceModeActive', { mType: message.mType }),
				);
			}
			message.data.msgId ??= uuidv4();
			const msgId: string = message.data.msgId;
			if (message.mType === 'iqmeshNetwork_AutoNetwork') {
				this.socket.send(message);
				return msgId;
			}
			await waitUntil(() => !monitorStore.getDataReadingInProgress && !monitorStore.getEnumInProgress);
			let timeout: number | null = null;
			if (options.timeout) {
				timeout = window.setTimeout(() => {
					this.removeMessage(msgId);
					options.callback();
					if (options.message === null) {
						return;
					}
					toast.error(options.message);
				}, options.timeout);
			}
			this.messages.push(new DaemonMessage(msgId, timeout));
			this.socket.send(message);
			return msgId;
		},
		/**
		 * Remove expected message ID and cancel timeout for callback
		 * @param {string | null} msgId Message ID
		 */
		removeMessage(msgId: string | null): void {
			if (msgId === null) {
				return;
			}
			const idx = this.messages.findIndex((message: DaemonMessage) => {
				return message.msgId === msgId;
			});
			if (idx === -1) {
				return;
			}
			window.clearTimeout(this.messages[idx].timeout);
			this.messages.splice(idx, 1);
		},
	},
	getters: {
		/**
		 * Returns connected state of socket
		 * @return {boolean} Socket connected state
		 */
		isConnected(): boolean {
			return this.connected;
		},
		/**
		 * Returns upstream ready state
		 * @return {boolean} Upstream ready state
		 */
		isUpstreamReady(): boolean {
			return this.upstream.status === UpstreamStatus.READY;
		},
		/**
		 * Returns upstream reconnecting state
		 * @return {boolean} Upstream reconnecting state
		 */
		isUpstreamReconnecting(): boolean {
			return this.upstream.status === UpstreamStatus.RECONNECTING;
		},
		/**
		 * Returns upstream status
		 * @return {UpstreamStatus} Upstream status
		 */
		upstreamStatus(): UpstreamStatus {
			return this.upstream.status;
		},
		/**
		 * Returns next upstream reconnect attempt timestamp
		 * @return {number|null} Next upstream reconnect attempt timestamp
		 */
		upstreamReconnectTs(): number|null {
			return this.upstream.nextAttempt;
		},
		/**
		 * Returns version of IQRF Gateway Daemon
		 * @return {string} IQRF Gateway Daemon verson
		 */
		getVersion(): string {
			return this.version;
		},
	},
});
