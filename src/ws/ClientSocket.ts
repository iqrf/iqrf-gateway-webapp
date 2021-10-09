import {Store} from 'vuex';

export interface ClientSocketOptions {
	url: string
	prefix: string;
	autoConnect?: boolean;
	reconnect?: boolean;
	reconnectDelay?: number;
}

class ClientSocket {
	private options: ClientSocketOptions = {
		url: 'localhost',
		prefix: '',
	};
	private store;
	private reconnectTimeout = 0;
	private client: WebSocket|null = null;

	/**
	 * Constructor
	 * @param url WebSocket server URL
	 * @param reconnect Reconnect automatically
	 * @param reconnectPeriod Reconnect attempt period
	 */
	constructor(options: ClientSocketOptions, store: Store<any>) {
		this.options = options;
		this.store = store;
		if (this.options.autoConnect) {
			this.client = this.connect();
		}
	}

	connect(): WebSocket {
		this.client = new WebSocket(this.options.url);
		this.registerEvents();
		return this.client;
	}

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

	close(): void {
		this.client?.close();
	}

	send(data: Record<string, any>): void {
		try {
			const message = JSON.stringify(data);
			this.client?.send(message);
			this.storeDispatch('SOCKET_ONSEND', data);
		} catch (e) {
			console.error(e);
		}
	}

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
