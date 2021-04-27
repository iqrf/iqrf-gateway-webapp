/**
 * Device backup data interface
 */
export interface IBackupData {
	/**
	 * Backup data
	 */
	data: string

	/**
	 * Device address
	 */
	deviceAddr: number

	/**
	 * DPA version
	 */
	dpaVer: number

	/**
	 * Module ID
	 */
	mid: number

	/**
	 * Is device online?
	 */
	online: boolean
}

/**
 * Device restore data interface
 */
export interface IRestoreData {
	/**
	 * Device address
	 */
	Address: string
	
	/**
	 * Coordinator data
	 */
	DataC?: string

	/**
	 * Node data
	 */
	DataN?: string

	/**
	 * Device type
	 */
	Device: string

	/**
	 * DPA version
	 */
	Version: string
}

/**
 * OTA upload configuration instance interface
 */
export interface IOtaUpload {
	/**
	 * Component name
	 */
	component: string
	
	/**
	 * Component instance name
	 */
	instance: string

	/**
	 * Upload path
	 */
	uploadPath: string

	/**
	 * Upload suffix
	 */
	uploadPathSuffix: string
}
