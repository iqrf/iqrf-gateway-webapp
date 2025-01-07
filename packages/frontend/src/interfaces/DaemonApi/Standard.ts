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
 * Stored sensor data interface
 */
export interface ISensor {
	/**
	 * Sensor type
	 */
	type: string

	/**
	 * Value read from senosr
	 */
	value?: number

	/**
	 * Unit
	 */
	unit: string

	/**
	 * Available FRC commands
	 */
	frcs?: Array<number>
}

/**
 * IQRF Sensor standard interface
 */
export interface IStandardSensor {
	/**
	 * Detailed information in case of more general sensor type
	 */
	breakdown?: IStandardSensor

	/**
	 * Value decimal plces
	 */
	decimalPlaces: number

	/**
	 * Available FRC commands
	 */
	frcs: Array<number>

	/**
	 * Sensor ID
	 */
	id: string

	/**
	 * Sensor name
	 */
	name: string

	/**
	 * Sensor abbreviation
	 */
	shortName: string

	/**
	 * Sensor type
	 */
	type: number

	/**
	 * Unit
	 */
	unit: string

	/**
	 * Value read from sensor
	 */
	value: number
}

/**
 * Stored sensor data interface
 */
export interface ISensor {
	/**
	 * Sensor type
	 */
	type: string

	/**
	 * Value read from senosr
	 */
	value?: number

	/**
	 * Unit
	 */
	unit: string

	/**
	 * Available FRC commands
	 */
	frcs?: Array<number>
}

/**
 * IQRF Sensor standard interface
 */
export interface IStandardSensor {
	/**
	 * Detailed information in case of more general sensor type
	 */
	breakdown?: IStandardSensor

	/**
	 * Value decimal plces
	 */
	decimalPlaces: number

	/**
	 * Available FRC commands
	 */
	frcs: Array<number>

	/**
	 * Sensor ID
	 */
	id: string

	/**
	 * Sensor name
	 */
	name: string

	/**
	 * Sensor abbreviation
	 */
	shortName: string

	/**
	 * Sensor type
	 */
	type: number

	/**
	 * Unit
	 */
	unit: string

	/**
	 * Value read from sensor
	 */
	value: number
}

/**
 * Light standard answer object
 */
export interface LightAnswer {
	status: number;
	value: number;
}

export interface LightAnswerResult extends LightAnswer {
	address: number;
	command: number;
}

export interface SetLaiResult {
	address: number;
	previousVoltage: number;
	currentVoltage: number;
}
