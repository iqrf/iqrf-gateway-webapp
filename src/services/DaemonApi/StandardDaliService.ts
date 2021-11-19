/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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
import store from '../../store';
import { WebSocketOptions } from '../../store/modules/daemonClient.module';

/**
 * IQRF Standard DALI service
 */
class StandardDaliService {

	/**
	 * Sends DALI commands to device specified by address.
	 * @param address Node address
	 * @param commands Array of DALI commands
	 * @param options WebSocket request option
	 * @return Message ID
	 */
	send(address: number, commands: number[], options: WebSocketOptions): Promise<string> {
		options.request = {
			'mType': 'iqrfDali_SendCommands',
			'data': {
				'req': {
					'nAdr': address,
					'param': {
						'commands': commands,
					},
				},
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemon_sendRequest', options);
	}

}

export default new StandardDaliService();
