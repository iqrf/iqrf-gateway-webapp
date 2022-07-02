/**
 * Copyright 2017-2022 IQRF Tech s.r.o.
 * Copyright 2019-2022 MICRORISC s.r.o.
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
import store from '@/store';
import {SecurityFormat} from '@/iqrfNet/securityFormat';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

/**
 * TR configuration security service
 */
class SecurityService {

	/**
	 * Sets IQMESH security
	 * @param nadr Network device address
	 * @param password
	 * @param inputFormat
	 * @param type
	 */
	setSecurity(nadr: number, password: string, inputFormat: SecurityFormat, type: number,
		timeout: number, message: string|null = null, callback: CallableFunction = () => {return;}): Promise<string> {
		const request = {
			'mType': 'iqrfEmbedOs_SetSecurity',
			'data': {
				'req': {
					'nAdr': nadr,
					'param': {
						'type': type,
						'data': this.convert(password, inputFormat),
					},
				},
				'returnVerbose': true,
			},
		};
		const options = new DaemonMessageOptions(request, timeout, message, callback);
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Converts an access password or an user key to HEX format
	 * @param password access password or user key
	 * @param format input data format (ASCII or HEX)
	 */
	convert(password: string, format: SecurityFormat): Array<number> {
		let data = password;
		if (format === SecurityFormat.ASCII) {
			data = '';
			for (let i = 0; i < password.length; ++i) {
				data += password.charCodeAt(i).toString(16);
			}
		}
		data = data.padEnd(32, '0');
		const array: Array<number> = [];
		for (let i = 0; i < 16; ++i) {
			array.push(parseInt(data.substr(i*2, 2), 16));
		}
		return array;
	}
}

export default new SecurityService();
