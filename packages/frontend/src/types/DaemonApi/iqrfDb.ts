/**
 * Database device brief interface
 */
export interface DbDeviceBrief {
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
export interface DbDevice extends DbDeviceBrief {
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

	sensors?: DbDeviceSensorDetail[]
}

export interface DbDeviceSensorDetail {
	index: number

	type: number

	name: string

	shortname: string

	unit: string

	frcs: number[]
}

export interface DbDeviceMetadata {
	address: number
	metadata: DbDeviceMetadataData
}

export interface DbDeviceMetadataData {
	name?: string|null
	location?: string|null
	other?: Record<string, any>
}

export interface DbDeviceSensors {
	/**
	 * Device address
	 */
	address: number

	/**
	 * Array of sensors
	 */
	sensors: DbSensorDetails[]
}

export interface DbSensorDetails {
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

/**
 * Enumeration response interface
 */
export interface DbEnumerateRsp {
	/// Enumeration step
	step: number;
	/// Enumeration step string
	stepStr: string;
}
