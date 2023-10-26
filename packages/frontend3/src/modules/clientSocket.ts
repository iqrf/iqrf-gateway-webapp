import type { DaemonApiRequest } from '@iqrf/iqrf-gateway-daemon-utils';

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
	 * @var {ClientSocketOptions} options Websocket client options
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
	private onOpenCallback: CallableFunction = () => {return;};

	/**
	 * @var {CallableFunction} onCloseCallback OnClose callback
	 */
	private onCloseCallback: CallableFunction = () => {return;};

	/**
	 * @var {CallableFunction} onErrorCallback OnError callback
	 */
	private onErrorCallback: CallableFunction = () => {return;};

	/**
	 * @var {CallableFunction} onMessageCallback OnMessage callback
	 */
	private onMessageCallback: CallableFunction = () => {return;};

	/**
	 * @var {CallableFunction} onSendCallback OnSend callback
	 */
	private onSendCallback: CallableFunction = () => {return;};

	/**
	 * Constructor
	 * @param {ClientSocketOptions} options WebSocket client options
	 * @param store Store
	 */
	constructor(options: ClientSocketOptions,
		onOpen: CallableFunction = () => {return;},
		onClose: CallableFunction = () => {return;},
		onError: CallableFunction = () => {return;},
		onMessage: CallableFunction = () => {return;},
		onSend: CallableFunction = () => {return;},
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
	setOnOpenCallback(callback: CallableFunction): void {
		if (this.socket === null) {
			return;
		}
		this.onOpenCallback = callback;
		// eslint-disable-next-line @typescript-eslint/no-unused-vars
		this.socket.onopen = function(_) {
			callback();
		};
	}

	/**
	 * Sets onclose callback
	 * @param {CallableFunction} callback Callback
	 */
	setOnCloseCallback(callback: CallableFunction): void {
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
	setOnErrorCallback(callback: CallableFunction): void {
		if (this.socket === null) {
			return;
		}
		this.onErrorCallback = callback;
		this.socket.onerror = function(event: Event) {
			callback(event);
		};
	}

	/**
	 * Sets onsend callback
	 * @param {CallableFunction} callback Callback
	 */
	setOnSendCallback(callback: CallableFunction): void {
		if (this.socket === null) {
			return;
		}
		this.onSendCallback = callback;
	}

	/**
	 * Sets onmessage callback
	 * @param {CallableFunction} callback Callback
	 */
	setOnMessageCallback(callback: CallableFunction): void {
		if (this.socket === null) {
			return;
		}
		this.onMessageCallback = callback;
		this.socket.onmessage = function(event: MessageEvent) {
			callback(event);
		};
	}

	/**
	 * Checks if socket is connected and open
	 */
	isConnected(): boolean {
		return this.socket?.readyState === WebSocket.OPEN;
	}

	/**
	 * Connects client to server
	 */
	connect(): WebSocket {
		this.socket = new WebSocket(this.options.url);
		this.socket.onopen = () => this.onOpenCallback();
		this.socket.onclose = (event: CloseEvent) => {
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
	reconnect(): void {
		clearTimeout(this.reconnectTimeout);
		this.reconnectTimeout = window.setTimeout(() => {
			this.connect();
		}, this.options.reconnectDelay);
	}

	/**
	 * Closes connection
	 */
	close(): void {
		this.socket?.close();
	}

	/**
	 * Sends a message
	 * @param data Message data
	 */
	send(data: DaemonApiRequest): void {
		try {
			const message = JSON.stringify(data);
			this.socket?.send(message);
			this.onSendCallback(data);
		} catch (e) {
			//
		}
	}

}
