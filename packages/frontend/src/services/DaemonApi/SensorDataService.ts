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
import { SensorDataMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { SensorDataSetConfigParams } from '@iqrf/iqrf-gateway-daemon-utils/types';

/**
 * SensorData service
 */
class SensorDataService {

	/**
	 * Get sensor data configuration
	 * @param {DaemonMessageOptions} options Websocket request options
	 * @returns {Promise<string>} Message ID
	 */
	getConfig(options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': SensorDataMessages.GetConfig,
			'data': {
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Set sensor data configuration
	 * @param {SensorDataSetConfigParams} config Sensor data configuration
	 * @param {DaemonMessageOptions} options Websocket request options
	 * @returns {Promise<string>} Message ID
	 */
	setConfig(config: SensorDataSetConfigParams, options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': SensorDataMessages.SetConfig,
			'data': {
				'req': config,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Get service status
	 * @param {DaemonMessageOptions} options Websocket request options
	 * @returns {Promise<string>} Message ID
	 */
	status(options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': SensorDataMessages.Status,
			'data': {}
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Start service
	 * @param {DaemonMessageOptions} options Websocket request options
	 * @returns {Promise<string>} Message ID
	 */
	start(options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': SensorDataMessages.Start,
			'data': {}
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Stop service
	 * @param {DaemonMessageOptions} options Websocket request options
	 * @returns {Promise<string>} Message ID
	 */
	stop(options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': SensorDataMessages.Stop,
			'data': {}
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Invoke worker
	 * @param {DaemonMessageOptions} options Websocket request options
	 * @returns {Promise<string>} Message ID
	 */
	invoke(options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': SensorDataMessages.Invoke,
			'data': {}
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

}

export default new SensorDataService();
