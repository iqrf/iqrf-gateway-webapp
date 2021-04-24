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
