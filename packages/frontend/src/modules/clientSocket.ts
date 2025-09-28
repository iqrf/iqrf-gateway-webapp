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

import { type DaemonApiRequest } from '@iqrf/iqrf-gateway-daemon-utils/types';

/**
 * Generic WebSocket state interface
 */
export interface GenericSocketState {
	/// WebSocket instance
	socket: InstanceType<typeof ClientSocket> | null;
	/// Is WebSocket connected?
	connected: boolean;
	/// Is WebSocket reconnecting?
	reconnecting: boolean;
	/// Is WebSocket reconnected?
	reconnected: boolean;
}

/**
 * WebSocket client options interface
 */
export interface ClientSocketOptions {
	/// WebSocket server URL
	url: string
	/// Auto-connect to server
	autoConnect?: boolean
	/// Reconnect to server
	reconnect?: boolean
	/// Reconnection delay
	reconnectDelay?: number
}

/// WebSocket on socket open callback
export type WsOnOpenCallback = () => void;

/// WebSocket on socket close callback
export type WsOnCloseCallback = (event: CloseEvent) => void;

/// WebSocket on message receive callback
export type WsOnMessageCallback = (event: MessageEvent) => void;

/// WebSocket on message send callback
export type WsOnSendCallback = (message: DaemonApiRequest) => void;

/// WebSocket on error callback
export type WsOnErrorCallback = (event: Event) => void;


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
	 * @var {WsOnOpenCallback} onOpenCallback On open callback
	 */
	private onOpenCallback: WsOnOpenCallback = (): void => { };

	/**
	 * @var {WsOnCloseCallback} onCloseCallback On close callback
	 */
	private onCloseCallback: WsOnCloseCallback = (): void => { };

	/**
	 * @var {WsOnErrorCallback} onErrorCallback On error callback
	 */
	private onErrorCallback: WsOnErrorCallback = (): void => { };

	/**
	 * @var {WsOnMessageCallback} onMessageCallback On message receive callback
	 */
	private onMessageCallback: WsOnMessageCallback = (): void => { };

	/**
	 * @var {WsOnSendCallback} onSendCallback On message send callback
	 */
	private onSendCallback: WsOnSendCallback = (): void => { };

	/**
	 * Constructor
	 * @param {ClientSocketOptions} options WebSocket client options
	 * @param {WsOnOpenCallback} onOpen On open callback
	 * @param {WsOnCloseCallback} onClose On close callback
	 * @param {WsOnErrorCallback} onError On error callback
	 * @param {WsOnMessageCallback} onMessage On message receive callback
	 * @param {WsOnSendCallback} onSend On message send callback
	 */
	public constructor(
		options: ClientSocketOptions,
		onOpen: WsOnOpenCallback = (): void => { },
		onClose: WsOnCloseCallback = (): void => { },
		onError: WsOnErrorCallback = (): void => { },
		onMessage: WsOnMessageCallback = (): void => { },
		onSend: WsOnSendCallback = (): void => { },
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
	 * Sets on open callback
	 * @param {WsOnOpenCallback} callback Callback
	 */
	public setOnOpenCallback(callback: WsOnOpenCallback): void {
		if (this.socket === null) {
			return;
		}
		this.onOpenCallback = callback;
		this.socket.onopen = function (): void {
			callback();
		};
	}

	/**
	 * Sets on close callback
	 * @param {WsOnCloseCallback} callback Callback
	 */
	public setOnCloseCallback(callback: WsOnCloseCallback): void {
		if (this.socket === null) {
			return;
		}
		this.onCloseCallback = callback;
		this.socket.onclose = (event: CloseEvent): void => {
			callback(event);
			if (this.options.reconnect) {
				this.reconnect();
			}
		};
	}

	/**
	 * Sets on error callback
	 * @param {WsOnErrorCallback} callback Callback
	 */
	public setOnErrorCallback(callback: WsOnErrorCallback): void {
		if (this.socket === null) {
			return;
		}
		this.onErrorCallback = callback;
		this.socket.onerror = function (event: Event): void {
			callback(event);
		};
	}

	/**
	 * Sets on message send callback
	 * @param {WsOnSendCallback} callback Callback
	 */
	public setOnSendCallback(callback: WsOnSendCallback): void {
		if (this.socket === null) {
			return;
		}
		this.onSendCallback = callback;
	}

	/**
	 * Sets on message receive callback
	 * @param {WsOnMessageCallback} callback Callback
	 */
	public setOnMessageCallback(callback: WsOnMessageCallback): void {
		if (this.socket === null) {
			return;
		}
		this.onMessageCallback = callback;
		this.socket.onmessage = function (event: MessageEvent): void {
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
		this.socket.onopen = (): void => {
			this.onOpenCallback();
		};
		this.socket.onclose = (event: CloseEvent): void => {
			this.onCloseCallback(event);
			if (this.options.reconnect) {
				this.reconnect();
			}
		};
		this.socket.onerror = (event: Event): void => {
			this.onErrorCallback(event);
		};
		this.socket.onmessage = (event: MessageEvent): void => {
			this.onMessageCallback(event);
		};
		return this.socket;
	}

	/**
	 * Reconnects after a failure
	 */
	public reconnect(): void {
		clearTimeout(this.reconnectTimeout);
		this.reconnectTimeout = window.setTimeout((): void => {
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
