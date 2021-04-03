/**
 * Systemd journal storage methods
 */
export enum StorageMethod {
	PERSISTENT = 'persistent',
	VOLATILE = 'volatile'
}

/**
 * Systemd journal time-based persistence units
 */
export enum TimeUnit {
	SECONDS = 's',
	MINUTES = 'm',
	HOURS = 'h',
	DAYS = 'day',
	WEEKS = 'week',
	MONTHS = 'month',
	YEAR = 'year'
}
