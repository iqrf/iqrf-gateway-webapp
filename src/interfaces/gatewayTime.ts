/**
 * Timezone information interface
 */
export interface ITimezone {
	/**
	 * Timezone name
	 */
	name: string

	/**
	 * Timezone code
	 */
	code: string

	/**
	 * Timezone offset
	 */
	offset: string
}

/**
 * Gateway time information interface
 */
export interface ITime extends ITimezone {
	/**
	 * Timestamp in seconds
	 */
	timestamp: number

	/**
	 * Sync time with NTP
	 */
	sync: boolean
}
