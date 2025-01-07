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
		this.isDev = import.meta.env.DEV;
		if (this.port !== '') {
			this.port = ':' + this.port;
		}
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
	 * Returns base URL
	 */
	getBaseUrl(): string {
		return window.location.protocol + '//' + this.hostname + (this.isDev ? ':8081' : this.port) + import.meta.env.VITE_BASE_URL;
	}

	/**
	 * Returns REST API URL
	 */
	getRestApiUrl(): string {
		if (import.meta.env.VITE_URL_REST_API?.length) {
			return import.meta.env.VITE_URL_REST_API;
		}
		return '//' + this.hostname + (this.isDev ? ':8080' : this.port) + import.meta.env.VITE_BASE_URL + 'api/v0/';
	}

	/**
	 * Returns WebSocket API URL
	 */
	getWsApiUrl(): string {
		if (import.meta.env.VITE_URL_DAEMON_API?.length) {
			return import.meta.env.VITE_URL_DAEMON_API;
		}
		return this.wsProtocol + this.hostname + (this.isDev ? ':1338': this.port + '/ws');
	}

	/**
	 * Returns WebSocket Monitor URL
	 */
	getWsMonitorUrl(): string {
		if (import.meta.env.VITE_URL_DAEMON_MONITOR?.length) {
			return import.meta.env.VITE_URL_DAEMON_MONITOR;
		}
		return this.wsProtocol + this.hostname + (this.isDev ? ':1438': this.port + '/wsMonitor');
	}

	/**
	 * Returns REST API URL from passed hostname
	 */
	getRestApiUrlFromAddr(hostname: string): string {
		return '//' + hostname + (this.isDev ? ':8080' : this.port) + import.meta.env.VITE_BASE_URL + 'api/v0/';
	}

}
