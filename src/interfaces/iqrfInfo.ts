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
	enumPeriod?: number
	
	/**
	 * Uniform DPA version and OS build according to coordinator?
	 */
	enumUniformDpaVer?: boolean

	/**
	 * Include metadata in messages?
	 */
	metaDataToMessages?: boolean
}
