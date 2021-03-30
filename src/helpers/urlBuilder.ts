/**
 * URL builder
 */
export default class UrlBuilder {

	/**
	 * Is development mode detected?
	 * @private
	 */
	private readonly isDev: boolean;

	/**
	 * Hostname
	 * @private
	 */
	private readonly hostname: string;

	/**
	 * Port
	 * @private
	 */
	private readonly port: string;

	/**
	 * Protocol for WebSocket - ws or wss
	 * @private
	 */
	private readonly wsProtocol: string;

	constructor() {
		const isHttps: boolean = window.location.protocol === 'https:';
		this.hostname = window.location.hostname;
		this.port = window.location.port;
		this.wsProtocol = (isHttps ? 'wss://' : 'ws://');
		this.isDev = this.port === '8081' && process.env.NODE_ENV === 'development';
		if (this.port !== '') {
			this.port = ':' + this.port;
		}
	}

	/**
	 * Development environment?
	 */
	isDevEnv(): boolean {
		return this.isDev;
	}

	/**
	 * Returns hostname
	 */
	getHostname(): string {
		return this.hostname;
	}

	/**
	 * Returns port
	 */
	getPort(): string {
		return this.port;
	}

	/**
	 * Returns REST API URL
	 */
	getRestApiUrl(): string {
		return '//' + this.hostname + (this.isDev ? ':8080' : this.port) + process.env.VUE_APP_BASE_URL + 'api/v0/';
	}

	/**
	 * Returns WebSocket API URL
	 */
	getWsApiUrl(): string {
		return this.wsProtocol + this.hostname + (this.isDev ? ':1338': this.port + '/ws');
	}

	/**
	 * Returns WebSocket Monitor URL
	 */
	getWsMonitorUrl(): string {
		return this.wsProtocol + this.hostname + (this.isDev ? ':1438': this.port + '/wsMonitor');
	}

	/**
	 * Returns REST API URL from passed hostname
	 */
	getRestApiUrlFromAddr(hostname: string): string {
		return '//' + hostname + (this.isDev ? ':8080' : this.port) + process.env.VUE_APP_BASE_URL + 'api/v0/';
	}

}
