export interface DbDeviceDataBrief {
	/**
	 * Device address
	 */
	address: number;
	/**
	 * Hardware profile ID
	 */
	hwpid: number;
}

export interface DbDeviceData extends DbDeviceDataBrief {
	/**
	 * Implemented binary outputs
	 */
	binouts?: DbBinoutData;
	/**
	 * Is device discovered?
	 */
	discovered: boolean;
	/**
	 * DPA version
	 */
	dpa: number;
	/**
	 * Hardware profile ID version
	 */
	hwpidVersion: number;
	/**
	 * Optional metadata
	 */
	metadata: Record<string, unknown> | null;
	/**
	 * Module ID
	 */
	mid: number;
	/**
	 * OS build
	 */
	osBuild: number;
	/**
	 * OS version
	 */
	osVersion: string;
	/**
	 * Parent address
	 */
	parent: number | null;
	/**
	 * Product name
	 */
	product: string | null;
	/**
	 * Implemented sensors
	 */
	sensors?: DbDeviceSensorDataBrief[];
	/**
	 * Virtual routing number
	 */
	vrn: number;
	/**
	 * Zone
	 */
	zone: number;
}

export interface DbBinoutData {
	/**
	 * Number of implemented binary outputs
	 */
	count: number;
}

export interface DbDeviceSensorDataBrief {
	/**
	 * Number of valid decimal places
	 */
	decimalPlaces: number;
	/**
	 * Implemented FRC commands
	 */
	frcs: number[];
	/**
	 * Sensor index
	 */
	index: number;
	/**
	 * Quantity name
	 */
	name: string;
	/**
	 * Quantity short name
	 */
	shortname: string;
	/**
	 * Sensor type
	 */
	type: number;
	/**
	 * Unit of measurement
	 */
	unit: string;
}

export interface DbSensors {
	/**
	 * Device address
	 */
	address: number;
	/**
	 * Sensor data
	 */
	sensors: DbSensorData[];
}

export interface DbSensorData {
	/**
	 * Valid decimal places
	 */
	decimalPlaces: number;
	/**
	 * 2-bit FRC implemented
	 */
	frc2Bit: boolean;
	/**
	 * 1-byte FRC implemented
	 */
	frc1Byte: boolean;
	/**
	 * 2-byte FRC implemented
	 */
	frc2Byte: boolean;
	/**
	 * 4-byte FRC implemented
	 */
	frc4Byte: boolean;
	/**
	 * Sensor index
	 */
	index: number;
	/**
	 * Quantity name
	 */
	name: string;
	/**
	 * Quantity short name
	 */
	shortname: string;
	/**
	 * Sensor type
	 */
	type: number;
	/**
	 * Unit of measurement
	 */
	unit: string;
	/**
	 * Last value timestamp
	 */
	updated: string | null;
	/**
	 * Last recorded value
	 */
	value: number | number[] | null;
}
