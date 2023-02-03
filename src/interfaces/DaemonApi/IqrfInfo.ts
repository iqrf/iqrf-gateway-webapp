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

/**
 * IQRF Info device base interface
 */
export interface IInfoDevice {
	/**
	 * Device address
	 */
	nAdr: number
}

/**
 * IQRF Info node interface
 */
export interface IInfoNode extends IInfoDevice {
	/**
	 * Device module ID
	 */
	mid: number
	
	/**
	 * Is device discovered?
	 */
	disc: boolean

	/**
	 * Device HWPID
	 */
	hwpid: number

	/**
	 * HWPID version
	 */
	hwpidVer: number

	/**
	 * Device os build
	 */
	osBuild: number

	/**
	 * Device DPA version
	 */
	dpaVer: number
}

/**
 * IQRF Info binout interface
 */
export interface IInfoBinout extends IInfoDevice {
	/**
	 * Number of binary outputs implemented by device
	 */
	binOuts: number
}

/**
 * IQRF Info lights interface
 */
export interface IInfoLight extends IInfoDevice {
	/**
	 * Number of lights implemented by device
	 */
	lights: number
}

/**
 * IQRF Info sensors interface
 */
export interface IInfoSensor extends IInfoDevice {
	/**
	 * Array of sensors implemented by device
	 */
	sensors: Array<IInfoSensorDetail>
}

/**
 * IQRF Info sensors detail interface
 */
export interface IInfoSensorDetail {
	/**
	 * Sensor index
	 */
	idx: number

	/**
	 * Sensor identifier
	 */
	id: string

	/**
	 * Sensor type number
	 */
	type: number

	/**
	 * Sensor name
	 */
	name: string

	/**
	 * Sensor abbreviation
	 */
	shortName: string

	/**
	 * Measurement unit
	 */
	unit: string

	/**
	 * Decimal places
	 */
	decimalPlaces: number

	/**
	 * Available FRCs
	 */
	frcs: Array<number>
}
