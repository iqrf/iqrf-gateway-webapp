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

import { DaemonMode } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { type DaemonApiRequest, type DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { DaemonMessage, type DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { defineStore } from 'pinia';
import { v4 as uuidv4 } from 'uuid';
import { toast } from 'vue3-toastify';

import UrlBuilder from '@/helpers/urlBuilder';
import ClientSocket, { type GenericSocketState } from '@/modules/clientSocket';
import i18n from '@/plugins/i18n';

import { useMonitorStore } from './monitorSocket';


interface DaemonState extends GenericSocketState {
	receivedMessages: number;
	requests: Record<string, string | DaemonApiRequest>;
	responses: Record<string, string | DaemonApiResponse>;
	messages: DaemonMessage[];
	version: string;
	versionMsgId: string;
	enum: boolean;
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
		receivedMessages: 0,
		requests: {},
		responses: {},
		messages: [],
		version: '',
		versionMsgId: '',
		enum: false,
	}),
	actions: {
		initSocket(): void {
			const urlBuilder = new UrlBuilder();
			this.socket = new ClientSocket(
				{
					url: urlBuilder.getDaemonApiUrl(),
					autoConnect: true,
					reconnect: true,
					reconnectDelay: 5000,
				},
				this.onOpen,
				this.onClose,
				this.onError,
				this.onMessage,
				this.onSend,
			);
		},
		sendVersionRequest(): void {
			this.versionMsgId = uuidv4();
			const request: DaemonApiRequest = {
				mType: 'mngDaemon_Version',
				data: {
					msgId: this.versionMsgId,
					returnVerbose: true,
				},
			};
			this.socket?.send(request);
			this.onSend(request);
		},
		sendMessage(options: DaemonMessageOptions): Promise<string> {
			const message = options.request;
			if (message === null) {
				return Promise.reject();
			}
			const monitorStore = useMonitorStore();
			if (monitorStore.mode === DaemonMode.Service && !serviceModeWhitelist.includes(message.mType)) {
				// TODO SERVICE MODE MODAL
			}
			let msgId = '';
			if (message.data.msgId === undefined) {
				message.data.msgId = uuidv4();
			}
			msgId = message.data.msgId;
			if (message.mType === 'iqmeshNetwork_AutoNetwork') {
				this.socket?.send(message);
				return Promise.resolve(msgId);
			}
			let timeout: number | null = null;
			if (options.timeout) {
				timeout = window.setTimeout(() => {
					this.removeMessage(msgId);
					options.callback();
					if (options.message === null) {
						return;
					}
					toast.error(i18n.global.t(options.message).toString());
				}, options.timeout);
			}
			this.messages.push(new DaemonMessage(msgId, timeout));
			this.socket?.send(message);
			return Promise.resolve(msgId);
		},
		/**
		 * On socket open action (used as callback)
		 */
		onOpen(): void {
			if (this.reconnecting) {
				setTimeout(() => {
					this.reconnecting = false;
					this.connected = true;
				}, 1000);
			} else {
				this.connected = true;
			}
		},
		/**
		 * On socket close action (used as callback)
		 * @param {CloseEvent} event Close event
		 */
		onClose(event: CloseEvent): void {
			console.error(event);
			this.connected = false;
			this.reconnecting = true;
		},
		/**
		 * On socket error action (used as callback)
		 * @param {Event} event Error event
		 */
		onError(event: Event): void {
			console.error(event);
		},
		/**
		 * On socket message action (used as callback)
		 * @param {MessageEvent<string>} event Message event
		 */
		onMessage(event: MessageEvent<string>): DaemonApiResponse {
			const message: DaemonApiResponse = JSON.parse(event.data) as DaemonApiResponse;
			if (message.mType === 'mngDaemon_Version' && message.data.msgId === this.versionMsgId) {
				const tokens = RegExp(/v\d+\.\d+\.\d+/g).exec(message.data.rsp.version as string);
				if (tokens !== null && tokens.length > 0) {
					this.version = tokens[0];
				}
			}
			if (message.mType === 'iqrfDb_Enumerate' && message.data.msgId === 'iqrfdb_enumerate_async') {
				if (message.data.rsp.step === 0) {
					this.enum = true;
				} else if (message.data.rsp.step === 8) {
					this.enum = false;
				}
			}
			this.responses[message.data.msgId] = message;
			return message;
		},
		onSend(message: DaemonApiRequest): void {
			if (message.data.msgId === undefined) {
				return;
			}
			this.requests[message.data.msgId] = message;
		},
		removeMessage(msgId: string): void {
			const idx = this.messages.findIndex((message: DaemonMessage) => {
				return message.msgId === msgId;
			});
			if (idx === -1) {
				return;
			}
			window.clearTimeout(this.messages[idx].timeout);
			this.messages.splice(idx, 1);
		},
		trimMessageQueue(): void {
			const overload = this.messages.slice(32, this.messages.length - 1);
			this.messages.splice(32, this.messages.length - 1);
			overload.forEach((message: DaemonMessage) => {
				if (message.msgId in this.requests) {
					delete this.requests[message.msgId];
				}
				if (message.msgId in this.responses) {
					delete this.responses[message.msgId];
				}
			});
		},
	},
	getters: {
		isConnected(): boolean {
			return this.connected;
		},
		getVersion(): string {
			return this.version;
		},
		isEnumerationRunning(): boolean {
			return this.enum;
		},
	},
});
