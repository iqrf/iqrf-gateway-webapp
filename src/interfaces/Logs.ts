/**
 * Gateway log entry
 */
export interface ILog {
	/**
	 * Is service or app available
	 */
	available: boolean

	/**
	 * Is log loaded?
	 */
	loaded: boolean

	/**
	 * Service log
	 */
	log: string
}
