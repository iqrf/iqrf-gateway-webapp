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
 * IQRF Standard Sensor service
 */
class StandardSensorService {

	/**
	 * Performs Sensor enumeration on device specified by address.
	 * @param address Node address
	 * @param options WebSocket request options
	 * @return Message ID
	 */
	enumerate(address: number, options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'iqrfSensor_Enumerate',
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
	 * Reads information from all sensors implemented by a device.
	 * @param address Node address
	 * @param options WebSocket request option
	 * @return Message ID
	 */
	readAll(address: number, options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'iqrfSensor_ReadSensorsWithTypes',
			'data': {
				'req': {
					'nAdr': address,
					'param': {
						'sensorIndexes': -1,
					},
				},
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

}

export default new StandardSensorService();
