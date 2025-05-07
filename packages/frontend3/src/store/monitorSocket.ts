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
import { type MonitorMessage } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { defineStore } from 'pinia';

import UrlBuilder from '@/helpers/urlBuilder';
import ClientSocket, { type GenericSocketState } from '@/modules/clientSocket';

interface MonitorState extends GenericSocketState {
	mode: DaemonMode;
	queueLength: number;
	lastTimestamp: number;
}

export const useMonitorStore = defineStore('monitor', {
	state: (): MonitorState => ({
		socket: null,
		connected: false,
		reconnecting: false,
		reconnected: false,
		mode: DaemonMode.Unknown,
		queueLength: 0,
		lastTimestamp: 0,
	}),
	actions: {
		initSocket() {
			const urlBuilder = new UrlBuilder();
			this.socket = new ClientSocket(
				{
					url: urlBuilder.getDaemonMonitorUrl(),
					autoConnect: true,
					reconnect: true,
					reconnectDelay: 5_000,
				},
			);
			this.socket.setOnOpenCallback(this.onOpen);
			this.socket.setOnCloseCallback(this.onClose);
			this.socket.setOnErrorCallback(this.onError);
			this.socket.setOnMessageCallback(this.onMessage);
		},
		/**
		 * Sets current Daemon mode
		 * @param {DaemonMode} mode Demon mode
		 */
		setMode(mode: DaemonMode): void {
			this.mode = mode;
		},
		/**
		 * On socket open action (used as callback)
		 */
		onOpen(): void {
			if (this.reconnecting) {
				this.reconnected = true;
			}
			this.connected = true;
		},
		/**
		 * On socket close action (used as callback)
		 * @param {CloseEvent} event Close event
		 */
		onClose(event: CloseEvent): void {
			console.error(event);
			this.connected = false;
			this.reconnecting = true;
			this.mode = DaemonMode.Unknown;
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
		onMessage(event: MessageEvent<string>): void {
			const message: MonitorMessage = JSON.parse(event.data) as MonitorMessage;
			this.queueLength = message.data.msgQueueLen;
			this.mode = message.data.operMode;
			this.lastTimestamp = message.data.timestamp;
		},
	},
	getters: {
		isConnected(): boolean {
			return this.connected;
		},
		/**
		 * Returns current Daemon mode
		 * @return {DaemonMode} Daemon mode
		 */
		getMode(): DaemonMode {
			return this.mode;
		},
		/**
		 * Returns current queue length
		 * @return {number} Queue length
		 */
		getQueueLength(): number {
			return this.queueLength;
		},
		/**
		 * Returns timestamp of last monitor notification
		 * @return {number} Last notification timestamp
		 */
		getLastTimestamp(): number {
			return this.lastTimestamp;
		},
	},
	persist: {
		pick: ['lastTimestamp'],
	},
});
