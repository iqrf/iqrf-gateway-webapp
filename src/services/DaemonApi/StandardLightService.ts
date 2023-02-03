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
import store from '@/store';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

export class StandardLight {

	/**
	 * Index of the light
	 */
	public index: number;

	/**
	 * Power level of the light from range <0;100>
	 */
	public power: number;

	/**
	 * Constructor
	 * @param index Index of the light
	 * @param power Power level of the light from range <0;100>
	 */
	public constructor(index: number, power: number) {
		this.index = index;
		this.power = power;
	}

}

/**
 * IQRF Standard light service
 */
class StandardLightService {

	/**
	 * Performs Light enumeration on device specified by address.
	 * @param address Node address
	 * @param options WebSocket request option
	 * @return Message ID
	 */
	enumerate(address: number, options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'iqrfLight_Enumerate',
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
	 * Decrements power of light at a device specified by address.
	 * @param address Node address
	 * @param lights Object containing light settings
	 * @param options WebSocket request option
	 * @return Message ID
	 */
	decrementPower(address: number, lights: StandardLight[], options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'iqrfLight_DecrementPower',
			'data': {
				'req': {
					'nAdr': address,
					'param': {
						'lights': lights,
					},
				},
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Increments power of light at a device specified by address.
	 * @param address Node address
	 * @param lights Object containing light settings
	 * @param options WebSocket request option
	 * @return Message ID
	 */
	incrementPower(address: number, lights: StandardLight[], options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'iqrfLight_IncrementPower',
			'data': {
				'req': {
					'nAdr': address,
					'param': {
						'lights': lights,
					},
				},
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Retrieves current power of a light specified by index.
	 * @param address Node address
	 * @param light Light index
	 * @param options WebSocket request option
	 * @return Message ID
	 */
	getPower(address: number, light: number, options: DaemonMessageOptions): Promise<string> {
		return this.setPower(address, [new StandardLight(light, 127)], options);
	}

	/**
	 * Sets power of lights at a device specified by address.
	 * @param address Node address
	 * @param lights Object containing light settings
	 * @param options WebSocket request option
	 * @return Message ID
	 */
	setPower(address: number, lights: StandardLight[], options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'iqrfLight_SetPower',
			'data': {
				'req': {
					'nAdr': address,
					'param': {
						'lights': lights,
					},
				},
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

}

export default new StandardLightService();
