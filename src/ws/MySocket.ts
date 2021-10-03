
interface MySocketOptions {
	url: string
	autoConnect?: boolean;
	reconnect?: boolean;
	reconnectDelay?: number;
}

class MySocket {
	private options: MySocketOptions = {
		url: 'localhost',
	};
	private store;
	private reconnectTimeout = 0;
	private prefix: string;
	private client: WebSocket|null = null;

	/**
	 * Constructor
	 * @param url WebSocket server URL
	 * @param reconnect Reconnect automatically
	 * @param reconnectPeriod Reconnect attempt period
	 */
	constructor(options: MySocketOptions, prefix: string, store) {
		this.options = options;
		this.prefix = prefix;
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
				this.storeDispatch(this.prefix + '_SOCKET_RECONNECT', null);
			}
			this.connect();
			this.registerEvents();
		}, this.options.reconnectDelay);
	}

	registerEvents(): void {
		const events: Array<string> = ['onopen', 'onmessage', 'onclose', 'onerror'];
		events.forEach((name: string) => {
			if (this.client === null) {
				return;
			}
			this.client[name] = (event) => {
				if (this.store) {
					const call = this.prefix + '_SOCKET_' + name;
					this.storeDispatch(call, event);
				}
				if (this.options.reconnect && name === 'onclose') {
					this.reconnect();
				}
			};
		});
	}

	storeDispatch(name: string, event): void {
		if (!name.includes('_SOCKET_')) {
			return;
		}
		let type = 'commit';
		let target = name.toUpperCase();
		let message = event;
		if (event && event.data) {
			message = JSON.parse(event.data);
			if (message.mutation) {
				type = 'commit';
				target = [message.namespace || '', message.mutation].filter((e) => !!e).join('/');
			} else if (message.action) {
				type = 'dispatch';
				target = [message.namespace || '', message.mutation].filter((e) => !!e).join('/');
			}
		}
		this.store[type](target, message);
	}

}

export default MySocket;
