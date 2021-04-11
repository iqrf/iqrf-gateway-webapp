/**
 * IQRF Info component instance interface
 */
export interface IIqrfInfo {
	/**
	 * Component name
	 */
	component: string
	
	/**
	 * Component instance name
	 */
	instance: string
	
	/**
	 * Enumerate network after startup?
	 */
	enumAtStartUp: boolean
	
	/**
	 * Enumeration period in minutes
	 */
	enumPeriod: number
	
	/**
	 * Uniform DPA version and OS build according to coordinator?
	 */
	enumUniformDpaVer: boolean

	/**
	 * Include metadata in messages?
	 */
	metaDataToMessages?: boolean
}

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
