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
