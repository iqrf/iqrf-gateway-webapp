/**
 * Copyright 2023-2021 IQRF Tech s.r.o.
 * Copyright 2023-2021 MICRORISC s.r.o.
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

import { type DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';

import { type DbDeviceMetadata } from '@/types/DaemonApi/iqrfDb';

/**
 * IQRF DB service
 */
export class DbService {

	/**
	 * Performs enumeration
	 * @param {DaemonMessageOptions} options Daemon message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static enumerate(options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: 'iqrfDb_Enumerate',
			data: {
				req: {
					reenumerate: true,
					standards: true,
				},
				returnVerbose: true,
			},
		};
		return options;
	}

	/**
	 * Resets Daemon database
	 * @param {DaemonMessageOptions} options Daemon message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static resetDatabase(options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: 'iqrfDb_Reset',
			data: {
				req: {},
				returnVerbose: true,
			},
		};
		return options;
	}

	/**
	 * Retrieves information about device from database
	 * @param {number} address Device address
	 * @param {boolean} brief Brief information
	 * @param {DaemonMessageOptions} options Daemon message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static getDevice(address: number, brief = false, options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: 'iqrfDb_GetDevice',
			data: {
				req: {
					address: address,
					brief: brief,
				},
				returnVerbose: true,
			},
		};
		return options;
	}

	/**
	 * Retrieves information about devices from database
	 * @param {boolean} brief Brief information
	 * @param {DaemonMessageOptions} options Daemon message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static getDevices(brief = false, options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: 'iqrfDb_GetDevices',
			data: {
				req: {
					brief: brief,
				},
				returnVerbose: true,
			},
		};
		return options;
	}

	/**
	 * Retrieves information about devices implementing BinaryOutput standard from database
	 * @param {DaemonMessageOptions} options Daemon message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static getBinaryOutputs(options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: 'iqrfDb_GetBinaryOutputs',
			data: {
				req: {},
				returnVerbose: true,
			},
		};
		return options;
	}

	/**
	 * Retrieves information about devices implementing Light standard from database
	 * @param {DaemonMessageOptions} options Daemon message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static getLights(options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: 'iqrfDb_GetLights',
			data: {
				req: {},
				returnVerbose: true,
			},
		};
		return options;
	}

	/**
	 * Retrieves information about devices implementing Sensor standard from database
	 * @param {DaemonMessageOptions} options Daemon message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static getSensors(options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: 'iqrfDb_GetSensors',
			data: {
				req: {},
				returnVerbose: true,
			},
		};
		return options;
	}

	/**
	 * Retrieves device metadata
	 * @param {number[]} devices Device addresses
	 * @param {DaemonMessageOptions} options Daemon message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static getMetadata(devices: number[], options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: 'iqrfDb_GetDeviceMetadata',
			data: {
				req: {
					devices: devices,
				},
				returnVerbose: true,
			},
		};
		return options;
	}

	/**
	 * Sets metadata for devices
	 * @param {DbDeviceMetadata[]} metadata Metadata
	 * @param {DaemonMessageOptions} options Daemon message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static setMetadata(metadata: DbDeviceMetadata[], options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: 'iqrfDb_SetDeviceMetadata',
			data: {
				req: {
					devices: metadata,
				},
				returnVerbose: true,
			},
		};
		return options;
	}
}
