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
import store from '@/store';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

/**
 * IQRF Standard light service
 */
class StandardLightService {

	/**
	 * Send LDI commands and return answers synchronously
	 * @param {number} address Node address
	 * @param {number[]} commands Commands to send
	 * @param {DaemonMessageOptions} options WebSocket request option
	 * @return {Promise<string>} Message ID
	 */
	sendLdiCommands(address: number, commands: number[], options: DaemonMessageOptions): Promise<string> {
		options.request = {
			mType: 'iqrfLight_SendLdiCommands',
			data: {
				req: {
					nAdr: address,
					param: {
						commands: commands,
					},
				},
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Set LAI voltage
	 * @param {number} address Node address
	 * @param {number} voltage Voltage to set
	 * @param {DaemonMessageOptions} options WebSocket request option
	 * @return {Promise<string>} Message ID
	 */
	setLaiVoltage(address: number, voltage: number, options: DaemonMessageOptions): Promise<string> {
		options.request = {
			mType: 'iqrfLight_SetLai',
			data: {
				req: {
					nAdr: address,
					param: {
						voltage: voltage,
					},
				},
				returnVerbose: true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}
}

export default new StandardLightService();
