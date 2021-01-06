/**
 * Troubleshoot entry interface
 */
export interface IEntry {
	/**
	 * Entry name
	 */
	item: string

	/**
	 * Current state
	 */
	state: string

	/**
	 * Suggested action
	 */
	action: string
}

/**
 * Troubleshoot feature interface
 */
export interface IFeature {
	/**
	 * Feature name
	 */
	title: string

	/**
	 * Troubleshoot entries
	 */
	entries: Array<IEntry>
}

/**
 * Troubleshoot feature REST API data interface
 */
export interface IFeatureData {
	/**
	 * Feature name
	 */
	name: string

	/**
	 * Is installed?
	 */
	installed: boolean

	/**
	 * Configuration exists
	 */
	config: boolean

	/**
	 * Set permission
	 */
	permission: number
}
