import { defineStore } from 'pinia';
import { MonitorMessage } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { DaemonMode } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import ClientSocket, { GenericSocketState } from '@/modules/clientSocket';
import UrlBuilder from '@/helpers/urlBuilder';

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
					reconnectDelay: 5000,
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
				this.reconnected;
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
		 * @param {MessageEvent} event Message event
		 */
		onMessage(event: MessageEvent): void {
			const message: MonitorMessage = JSON.parse(event.data);
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
		 * @param {MonitorState} state Monitor state
		 * @returns {DaemonMode} Daemon mode
		 */
		getMode(): DaemonMode {
			return this.mode;
		},
		/**
		 * Returns current queue length
		 * @param {MonitorState} state Monitor state
		 * @returns {number} Queue length
		 */
		getQueueLength(): number {
			return this.queueLength;
		},
		/**
		 * Returns timestamp of last monitor notification
		 * @param {MonitorState} state Monitor state
		 * @returns {number} Last notification timestamp
		 */
		getLastTimestamp(): number {
			return this.lastTimestamp;
		},
	},
	persist: {
		paths: ['lastTimestamp'],
	},
});

