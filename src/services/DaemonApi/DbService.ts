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
import { IqrfDbDeviceMetadata } from '@/interfaces/DaemonApi/IqrfDb';
import store from '@/store';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

/**
 * IQRF DB service
 */
class DbService {
	/**
	 * Performs enumeration
	 * @param {DaemonMessageOptions} options Daemon message options
	 * @return {Promise<string>} Message ID
	 */
	enumerate(options: DaemonMessageOptions): Promise<string> {
		const request = {
			mType: 'iqrfDb_Enumerate',
			data: {
				req: {
					reenumerate: true,
					standards: true,
				},
				returnVerbose: true,
			},
		};
		options.request = request;
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Resets Daemon database
	 * @param {DaemonMessageOptions} options Daemon message options
	 * @return {Promise<string>} Message ID
	 */
	resetDatabase(options: DaemonMessageOptions): Promise<string> {
		const request = {
			mType: 'iqrfDb_Reset',
			data: {
				req: {},
				returnVerbose: true,
			},
		};
		options.request = request;
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Retrieves information about devices from database
	 * @param {boolean} brief Brief information
	 * @param {DaemonMessageOptions} options Daemon message options
	 * @return {Promise<string>} Message ID
	 */
	getDevices(brief: boolean = false, options: DaemonMessageOptions): Promise<string> {
		const request = {
			mType: 'iqrfDb_GetDevices',
			data: {
				req: {
					brief: brief,
				},
				returnVerbose: true,
			},
		};
		options.request = request;
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Retrieves information about devices implementing BinaryOutput standard from database
	 * @param {DaemonMessageOptions} options Daemon message options
	 * @return {Promise<string>} Message ID
	 */
	getBinaryOutputs(options: DaemonMessageOptions): Promise<string> {
		const request = {
			mType: 'iqrfDb_GetBinaryOutputs',
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
	 * @return {Promise<string>} Message ID
	 */
	getLights(options: DaemonMessageOptions): Promise<string> {
		const request = {
			mType: 'iqrfDb_GetLights',
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
	 * @return {Promise<string>} Message ID
	 */
	getSensors(options: DaemonMessageOptions): Promise<string> {
		const request = {
			mType: 'iqrfDb_GetSensors',
			data: {
				req: {},
				returnVerbose: true,
			},
		};
		options.request = request;
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Get device metadata
	 * @param {number[]} devices Device addresses
	 * @param {DaemonMessageOptions} options Daemon message options
	 * @return {Promise<string>} Message ID
	 */
	getMetadata(devices: number[], options: DaemonMessageOptions): Promise<string> {
		options.request = {
			mType: 'iqrfDb_GetDeviceMetadata',
			data: {
				req: {
					devices: devices,
				},
				returnVerbose: true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Set device metadata
	 * @param {IqrfDbDeviceMetadata[]} metadata Device metadata
	 * @param {DaemonMessageOptions} options Daemon message options 
	 * @return {Promise<string>} Message ID
	 */
	setMetadata(metadata: IqrfDbDeviceMetadata[], options: DaemonMessageOptions): Promise<string> {
		options.request = {
			mType: 'iqrfDb_SetDeviceMetadata',
			data: {
				req: {
					devices: metadata,
				},
				returnVerbose: true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}
}

export default new DbService();
