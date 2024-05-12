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


/**
 * WebSocket protocol
 */
export enum Protocol {
	/// Plain-text WebSocket
	WS = 'ws:',
	/// TLS encrypted WebSocket
	WSS = 'wss:',
}

/**
 * WebSocket URL
 */
export interface ParsedUrl {
	/// Protocol
	protocol: Protocol;
	/// Hostname
	hostname: string;
	/// Port
	port: number|null;
	/// Path
	path: string|null;
}

/**
 * URL helper
 */
export class UrlHelper {

	/**
	 * Parse URL from string
	 * @param {string} str URL string to parse
	 * @returns {ParsedUrl} Parsed URL object
	 * @throws {Error} Invalid URL protocol
	 * @throws {TypeError} Invalid URL
	 */
	public static fromString(str: string): ParsedUrl {
		const url: URL = new URL(str);
		const protocol: Protocol = url.protocol as Protocol;
		if (!Object.values(Protocol).includes(protocol)) {
			throw new Error('Invalid WebSocket URL protocol');
		}
		const path: string = url.pathname.replace(/^\//, '');
		return {
			protocol: protocol,
			hostname: url.hostname,
			port: url.port !== '' ? Number.parseInt(url.port) : null,
			path: path !== '' ? path : null,
		};
	}

	/**
	 * Convert URL object to string
	 * @param {ParsedUrl} parsedUrl URL object
	 * @returns {string} URL string
	 */
	public static toString(parsedUrl: ParsedUrl): string {
		let url = `${parsedUrl.protocol}//${parsedUrl.hostname}`;
		if (parsedUrl.port !== null) {
			url += `:${parsedUrl.port}`;
		}
		if (parsedUrl.path !== null && parsedUrl.path !== '') {
			url += `/${parsedUrl.path}`;
		}
		return url;
	}

}
