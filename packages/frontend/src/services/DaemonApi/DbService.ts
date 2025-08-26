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
import { DatabaseMessage } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import {
	DatabaseGetDeviceMetadataParams,
	type DatabaseGetDevicesParams,
	type DatabaseSetDeviceMetadataParams,
} from '@iqrf/iqrf-gateway-daemon-utils/types';

/**
 * IQRF DB service
 */
class DbService {
	/**
	 * Performs enumeration
	 * @param {DaemonMessageOptions} options Daemon message options
	 * @returns {Promise<string>} Message ID
	 */
	enumerate(options: DaemonMessageOptions): Promise<string> {
		options.request = {
			mType: DatabaseMessage.Enumerate,
			data: {
				req: {
					reenumerate: true,
					standards: true,
				},
				returnVerbose: true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Resets Daemon database
	 * @param {DaemonMessageOptions} options Daemon message options
	 * @returns {Promise<string>} Message ID
	 */
	resetDatabase(options: DaemonMessageOptions): Promise<string> {
		options.request = {
			mType: DatabaseMessage.Reset,
			data: {
				req: {},
				returnVerbose: true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Retrieves information about devices from database
	 * @param {DatabaseGetDevicesParams} params Parameters
	 * @param {DaemonMessageOptions} options Daemon message options
	 * @returns {Promise<string>} Message ID
	 */
	getDevices(params: DatabaseGetDevicesParams, options: DaemonMessageOptions): Promise<string> {
		const request = {
			mType: DatabaseMessage.GetDevices,
			data: {
				req: params,
				returnVerbose: true,
			},
		};
		options.request = request;
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Retrieves information about devices implementing BinaryOutput standard from database
	 * @param {DaemonMessageOptions} options Daemon message options
	 * @returns {Promise<string>} Message ID
	 */
	getBinaryOutputs(options: DaemonMessageOptions): Promise<string> {
		const request = {
			mType: DatabaseMessage.GetBinaryOutputs,
			data: {
				req: {},
				returnVerbose: true,
			},
		};
		options.request = request;
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Retrieves information about devices implementing Light standard from database
	 * @param {DaemonMessageOptions} options Daemon message options
	 * @returns {Promise<string>} Message ID
	 */
	getLights(options: DaemonMessageOptions): Promise<string> {
		const request = {
			mType: DatabaseMessage.GetLights,
			data: {
				req: {},
				returnVerbose: true,
			},
		};
		options.request = request;
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Retrieves information about devices implementing Sensor standard from database
	 * @param {DaemonMessageOptions} options Daemon message options
	 * @returns {Promise<string>} Message ID
	 */
	getSensors(options: DaemonMessageOptions): Promise<string> {
		const request = {
			mType: DatabaseMessage.GetSensors,
			data: {
				req: {},
				returnVerbose: true,
			},
		};
		options.request = request;
		return store.dispatch('daemonClient/sendRequest', options);
	}

	getMetadata(params: DatabaseGetDeviceMetadataParams, options: DaemonMessageOptions): Promise<string> {
		options.request = {
			mType: 'iqrfDb_GetDeviceMetadata',
			data: {
				req: params,
				returnVerbose: true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	setMetadata(params: DatabaseSetDeviceMetadataParams, options: DaemonMessageOptions): Promise<string> {
		options.request = {
			mType: 'iqrfDb_SetDeviceMetadata',
			data: {
				req: params,
				returnVerbose: true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}
}

export default new DbService();
