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
 * Daemon API message class
 */
class DaemonMessage {
	/**
	 * Message ID
	 */
	public msgId: string;

	/**
	 * Request timeout
	 */
	public timeout: number|undefined;

	/**
	 * Constructor
	 * @param {string} msgId Message ID
	 * @param {number|null} timeout Request timeout
	 */
	constructor(msgId: string, timeout: number|null = null) {
		this.msgId = msgId;
		this.timeout = timeout ?? undefined;
	}
}

export default DaemonMessage;
