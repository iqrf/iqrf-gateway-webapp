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

import { type DaemonApiRequest } from '@iqrf/iqrf-gateway-daemon-utils/types';

export interface GenericSocketState {
	socket: InstanceType<typeof ClientSocket> | null;
	connected: boolean;
	reconnecting: boolean;
	reconnected: boolean;
}

export interface ClientSocketOptions {
	url: string
	autoConnect?: boolean
	reconnect?: boolean
	reconnectDelay?: number
}

export default class ClientSocket {

	/**
	 * @property {ClientSocketOptions} options Websocket client options
	 */
	private options: ClientSocketOptions = {
		url: 'localhost',
	};

	/**
	 * @var {number} reconnectTimeout Client reconnect timeout
	 */
	private reconnectTimeout = 0;

	/**
	 * @var {WebSocket|null} client WebSocket object
	 */
	private socket: WebSocket | null = null;

	/**
	 * @var {CallableFunction} onOpenCallback OnOpen callback
	 */
	private onOpenCallback: CallableFunction = (): void => {return;};

	/**
	 * @var {CallableFunction} onCloseCallback OnClose callback
	 */
	private onCloseCallback: CallableFunction = (): void => {return;};

	/**
	 * @var {CallableFunction} onErrorCallback OnError callback
	 */
	private onErrorCallback: CallableFunction = (): void => {return;};

	/**
	 * @var {CallableFunction} onMessageCallback OnMessage callback
	 */
	private onMessageCallback: CallableFunction = (): void => {return;};

	/**
	 * @var {CallableFunction} onSendCallback OnSend callback
	 */
	private onSendCallback: CallableFunction = (): void => {return;};

	/**
	 * Constructor
	 * @param {ClientSocketOptions} options WebSocket client options
	 * @param {CallableFunction} onOpen On open callback
	 * @param {CallableFunction} onClose On close callback
	 * @param {CallableFunction} onError On error callback
	 * @param {CallableFunction} onMessage On message callback
	 * @param {CallableFunction} onSend On send callback
	 */
	public constructor(
		options: ClientSocketOptions,
		onOpen: CallableFunction = (): void => {return;},
		onClose: CallableFunction = (): void => {return;},
		onError: CallableFunction = (): void => {return;},
		onMessage: CallableFunction = (): void => {return;},
		onSend: CallableFunction = (): void => {return;},
	) {
		this.options = options;
		this.onOpenCallback = onOpen;
		this.onCloseCallback = onClose;
		this.onErrorCallback = onError;
		this.onMessageCallback = onMessage;
		this.onSendCallback = onSend;
		if (this.options.autoConnect) {
			this.socket = this.connect();
		}
	}

	/**
	 * Sets onopen callback
	 * @param {CallableFunction} callback Callback
	 */
	public setOnOpenCallback(callback: CallableFunction): void {
		if (this.socket === null) {
			return;
		}
		this.onOpenCallback = callback;
		// eslint-disable-next-line @typescript-eslint/no-unused-vars
		this.socket.onopen = function (_): void {
			callback();
		};
	}

	/**
	 * Sets onclose callback
	 * @param {CallableFunction} callback Callback
	 */
	public setOnCloseCallback(callback: CallableFunction): void {
		if (this.socket === null) {
			return;
		}
		this.onCloseCallback = callback;
		this.socket.onclose = (event: CloseEvent) => {
			callback(event);
			if (this.options.reconnect) {
				this.reconnect();
			}
		};
	}

	/**
	 * Sets onerror callback
	 * @param {CallableFunction} callback Callback
	 */
	public setOnErrorCallback(callback: CallableFunction): void {
		if (this.socket === null) {
			return;
		}
		this.onErrorCallback = callback;
		this.socket.onerror = function (event: Event) {
			callback(event);
		};
	}

	/**
	 * Sets onsend callback
	 * @param {CallableFunction} callback Callback
	 */
	public setOnSendCallback(callback: CallableFunction): void {
		if (this.socket === null) {
			return;
		}
		this.onSendCallback = callback;
	}

	/**
	 * Sets onmessage callback
	 * @param {CallableFunction} callback Callback
	 */
	public setOnMessageCallback(callback: CallableFunction): void {
		if (this.socket === null) {
			return;
		}
		this.onMessageCallback = callback;
		this.socket.onmessage = function (event: MessageEvent) {
			callback(event);
		};
	}

	/**
	 * Checks if socket is connected and open
	 * @return {boolean} Is connected
	 */
	public isConnected(): boolean {
		return this.socket?.readyState === WebSocket.OPEN;
	}

	/**
	 * Connects client to server
	 * @return {WebSocket} WebSocket socket
	 */
	public connect(): WebSocket {
		this.socket = new WebSocket(this.options.url);
		this.socket.onopen = () => this.onOpenCallback();
		this.socket.onclose = (event: CloseEvent): void => {
			this.onCloseCallback(event);
			if (this.options.reconnect) {
				this.reconnect();
			}
		};
		this.socket.onerror = (event: Event) => this.onErrorCallback(event);
		this.socket.onmessage = (event: MessageEvent) => this.onMessageCallback(event);
		return this.socket;
	}

	/**
	 * Reconnects after a failure
	 */
	public reconnect(): void {
		clearTimeout(this.reconnectTimeout);
		this.reconnectTimeout = window.setTimeout(() => {
			this.connect();
		}, this.options.reconnectDelay);
	}

	/**
	 * Closes connection
	 */
	public close(): void {
		this.socket?.close();
	}

	/**
	 * Sends a message
	 * @param {DaemonApiRequest} data Message data
	 */
	public send(data: DaemonApiRequest): void {
		try {
			const message: string = JSON.stringify(data);
			this.socket?.send(message);
			this.onSendCallback(data);
		} catch {
			//
		}
	}

}
