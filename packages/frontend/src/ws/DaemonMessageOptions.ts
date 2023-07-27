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

import {TranslateResult} from 'vue-i18n';

/**
 * Daemon request options class
 */
class DaemonMessageOptions {
	/**
	 * Request object
	 */
	public request: Record<string, any>|null;

	/**
	 * Request timeout
	 */
	public timeout: number|null;

	/**
	 * Request timeout toast message
	 */
	public message: TranslateResult|string|null;

	/**
	 * Request timeout callback
	 */
	public callback: CallableFunction;

	/**
	 * Constructor
	 * @param {Record<string, any>|null} request Request object
	 * @param {number|null} timeout Request timeout
	 * @param {TranslateResult|string|null} message Request timeout toast message
	 * @param {CallableFunction} callback Request timeout callback
	 */
	constructor(request: Record<string, any>|null, timeout: number|null = null, message: TranslateResult|string|null = null, callback: CallableFunction = () => {return;}) {
		this.request = request;
		this.timeout = timeout;
		this.message = message;
		this.callback = callback;
	}
}

export default DaemonMessageOptions;
