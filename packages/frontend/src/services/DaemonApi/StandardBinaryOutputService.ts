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

export class StandardBinaryOutput {

	/**
	 * Index of the binary output
	 */
	public index: number;

	/**
	 * State of the binary output
	 */
	public state: boolean;

	/**
	 * Constructor
	 * @param index Index of the binary output
	 * @param state State of the binary output
	 */
	public constructor(index: number, state: boolean) {
		this.index = index;
		this.state = state;
	}
}

/**
 * IQRF Standard binary output service
 */
class StandardBinaryOutputService {

	/**
	 * Performs Binary Output enumeration on device specified by address.
	 * @param address Node address
	 * @param options WebSocket request options
	 * @return Message ID
	 */
	enumerate(address: number, options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'iqrfBinaryoutput_Enumerate',
			'data': {
				'req': {
					'nAdr': address,
					'param': {},
				},
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Retrieves states of binary outputs.
	 * @param address Node address
	 * @param options WebSocket request options
	 * @return Message ID
	 */
	getOutputs(address: number, options: DaemonMessageOptions): Promise<string> {
		return this.setOutputs(address, [], options);
	}

	/**
	 * Sets binary outputs to a specified state.
	 * If no output settings are specified, only previous states of binary outputs are retrieved.
	 * @param address Node address
	 * @param outputs New output setting
	 * @param options WebSocket request options
	 * @return Message ID
	 */
	setOutputs(address: number, outputs: StandardBinaryOutput[] = [], options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'iqrfBinaryoutput_SetOutput',
			'data': {
				'req': {
					'nAdr': address,
					'param': {
						'binOuts': outputs,
					},
				},
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}
}

export default new StandardBinaryOutputService();
