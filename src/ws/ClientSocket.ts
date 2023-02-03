/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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

/**
 * Websocket options
 */
export interface ClientSocketOptions {
	url: string;
	prefix: string;
	autoConnect?: boolean;
	reconnect?: boolean;
	reconnectDelay?: number;
}

/**
 * WebSocket client class
 */
class ClientSocket {
	/**
	 * @var {ClientSocketOptions} options WebSocket client options
	 */
	private options: ClientSocketOptions = {
		url: 'localhost',
		prefix: '',
	};

	/**
	 * Store
	 */
	private store;

	/**
	 * @var {number} reconnectTimeout Client reconnect timeout
	 */
	private reconnectTimeout = 0;

	/**
	 * @var {WebSocket|null} client WebSocket object
	 */
	private client: WebSocket | null = null;

	/**
	 * Constructor
	 * @param {ClientSocketOptions} options WebSocket client options
	 * @param store Store
	 */
	constructor(options: ClientSocketOptions, store) {
		this.options = options;
		this.store = store;
		if (this.options.autoConnect) {
			this.client = this.connect();
		}
	}

	/**
	 * Connects client to server
	 */
	connect(): WebSocket {
		this.client = new WebSocket(this.options.url);
		this.registerEvents();
		return this.client;
	}

	/**
	 * Reconnects after a failure
	 */
	reconnect(): void {
		clearTimeout(this.reconnectTimeout);
		this.reconnectTimeout = window.setTimeout(() => {
			if (this.store) {
				this.storeDispatch('SOCKET_RECONNECT', null);
			}
			this.connect();
			this.registerEvents();
		}, this.options.reconnectDelay);
	}

	/**
	 * Closes connection
	 */
	close(): void {
		this.client?.close();
	}

	/**
	 * Sends a message
	 * @param data Message data
	 */
	send(data: Record<string, any>): void {
		try {
			const message = JSON.stringify(data);
			this.client?.send(message);
			this.storeDispatch('SOCKET_ONSEND', data);
		} catch (e) {
			console.error(e);
		}
	}

	/**
	 * Registers websocket event callbacks
	 */
	registerEvents(): void {
		const events: Array<string> = ['onopen', 'onmessage', 'onclose', 'onerror'];
		events.forEach((name: string) => {
			if (this.client === null) {
				return;
			}
			this.client[name] = (event) => {
				if (this.store) {
					const call = 'SOCKET_' + name;
					this.storeDispatch(call, event);
				}
				if (this.options.reconnect && name === 'onclose') {
					this.reconnect();
				}
			};
		});
	}

	/**
	 * Dispatches an event to store
	 * @param name Event name
	 * @param event Event
	 */
	storeDispatch(name: string, event): void {
		if (!name.includes('SOCKET_')) {
			return;
		}
		let type = 'commit';
		const target = this.options.prefix + name.toUpperCase();
		let message = event;
		if (event && event.data) {
			if (typeof event.data === 'string') {
				message = JSON.parse(event.data);
			}
			if (message.mutation) {
				type = 'commit';
			} else if (message.action) {
				type = 'dispatch';
			}
		}
		this.store[type](target, message);
	}
}

export default ClientSocket;
