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
import store from '@/store';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

/**
 * OS service
 */
class OsService {

	/**
	 * Disables Custom DPA handler in TR configuration
	 * @param address Device address
	 * @param timeout Request timeout in milliseconds
	 * @param message Request timeout message
	 * @param callback Request timeout callback
	 * @return Request message ID
	 */
	disableHandler(address: number, timeout: number, message: string|null = null, callback: CallableFunction = () => {return;}): Promise<string> {
		const request = {
			'mType': 'iqrfEmbedOs_WriteCfgByte',
			'data': {
				'req': {
					'nAdr': address,
					'param': {
						'bytes': [
							{
								'address': 5,
								'value': 128,
								'mask': 255,
							},
						],
					},
				},
				'returnVerbose': true,
			},
		};
		const options = new DaemonMessageOptions(request, timeout, message, callback);
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Sends OS Read request
	 * @param address Address
	 * @param timeout Timeout in milliseconds
	 * @param message Timeout message
	 * @param callback Timeout callback
	 * @return Message ID
	 */
	read(address: number, timeout: number, message: string|null = null, callback: CallableFunction = () => {return;}): Promise<string> {
		const request = {
			'mType': 'iqrfEmbedOs_Read',
			'data': {
				'req': {
					'nAdr': address,
					'param': {},
				},
			},
		};
		const options = new DaemonMessageOptions(request, timeout, message, callback);
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Sends OS Reset request
	 * @param address Device address
	 * @param timeout Request timeout in milliseconds
	 * @param message Request timeout message
	 * @param callback Request timeout callback
	 * @return Request message ID
	 */
	reset(address: number, timeout: number, message: string|null = null, callback: CallableFunction = () => {return;}): Promise<string> {
		const request = {
			'mType': 'iqrfEmbedOs_Reset',
			'data': {
				'req': {
					'nAdr': address,
					'param': {},
				},
				'returnVerbose': true,
			},
		};
		const options = new DaemonMessageOptions(request, timeout, message, callback);
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Sends OS restart request
	 * @param address Device address
	 * @param hwpid HWPID
	 * @param timeout Request timeout in milliseconds
	 * @param message Request timeout message
	 * @param callback Request timeout callback
	 * @return Request message ID
	 */
	restart(address: number, hwpid: number|null, timeout: number, message: string|null = null, callback: CallableFunction = () => {return;}): Promise<string> {
		const request = {
			'mType': 'iqrfEmbedOs_Restart',
			'data': {
				'req': {
					'nAdr': address,
					'param': {},
				},
				'returnVerbose': true,
			},
		};
		if (hwpid) {
			Object.assign(request.data.req, {hwpId: hwpid});
		}
		const options = new DaemonMessageOptions(request, timeout, message, callback);
		return store.dispatch('daemonClient/sendRequest', options);
	}

}

export default new OsService();
