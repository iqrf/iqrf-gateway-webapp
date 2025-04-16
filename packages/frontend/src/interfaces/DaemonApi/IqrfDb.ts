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

/**
 * Database device brief interface
 */
export interface IIqrfDbDeviceBrief {
	/**
	 * Device address
	 */
	address: number

	/**
	 * Device HWPID
	 */
	hwpid: number

	/**
	 * Device name
	 */
	name: string|null

	/**
	 * Device location
	 */
	location: string|null
}


/**
 * Database device record interface
 */
export interface IIqrfDbDeviceFull extends IIqrfDbDeviceBrief {
	/**
	 * Device module ID
	 */
	mid: number

	/**
	 * Device HWPID version
	 */
	hwpidVersion: number

	/**
	 * Device OS build
	 */
	osBuild: number

	/**
	 * Device OS version
	 */
	osVersion: string

	/**
	 * Device DPA version
	 */
	dpa: number

	/**
	 * Is device discovered?
	 */
	discovered: boolean

	/**
	 * Device virtual routing number
	 */
	vrn: number

	/**
	 * Device zone
	 */
	zone: number

	/**
	 * Parent device
	 */
	parent: number|null
}

/**
 * Database binary output record interface
 */
export interface IIqrfDbBo {
	/**
	 * Device address
	 */
	address: number

	/**
	 * Number of implemented binary outputs
	 */
	count: number
}

/**
 * Database sensor record interface
 */
export interface IIqrfDbSensor {
	/**
	 * Device address
	 */
	address: number

	/**
	 * Array of sensors
	 */
	sensors: Array<IIqrfDbSensorDetails>
}

/**
 * Database sensor details interface
 */
export interface IIqrfDbSensorDetails {
	/**
	 * Sensor index
	 */
	index: number

	/**
	 * Sensor type
	 */
	type: number

	/**
	 * Sensor name
	 */
	name: string

	/**
	 * Sensor shortname
	 */
	shortname: string

	/**
	 * Quantity unit
	 */
	unit: string

	/**
	 * Unit decimal places
	 */
	decimalPlaces: number

	/**
	 * Implements 2 bit FRC command
	 */
	frc2Bit: boolean

	/**
	 * Implements 1 byte FRC command
	 */
	frc1Byte: boolean

	/**
	 * Implements 2 byte FRC command
	 */
	frc2Byte: boolean

	/**
	 * Implements 4 byte FRC command
	 */
	frc4Byte: boolean

	/**
	 * Last measured value
	 */
	value: number|null

	/**
	 * Last updated
	 */
	updated: string|null
}

export interface IIqrfDbDeviceMetadata {
	address: number
	metadata: IIqrfDbMetadata
}

export interface IIqrfDbMetadata {
	name?: string|null
	location?: string|null
	other?: Record<string, any>
}
