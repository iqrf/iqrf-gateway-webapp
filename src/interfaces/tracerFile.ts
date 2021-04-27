/**
 * VerbosityLevels interface for Logging service component instance
 */
export interface IVerbosityLevel {
	/**
	 * Verbosity level channel
	 */
	channel: number

	/**
	 * Verbosity severity
	 */
	level: string
}

/**
 * Logging service component instance interface
 */
export interface ITracerFile {
	/**
	 * Component name
	 */
	component: string

	/**
	 * Name of log file
	 */
	filename: string

	/**
	 * Component instance name
	 */
	instance: string

	/**
	 * Maximum log file size
	 */
	maxSizeMB: number

	/**
	 * Maximum lifespan of timestamped files in minutes (Daemon version >= 2.3.0)
	 */
	maxAgeMinutes: number

	/**
	 * Maximum number of timestamped files (Daemon version >= 2.3.0)
	 */
	maxNumber: number

	/**
	 * Path to directory with log files
	 */
	path: string

	/**
	 * Should log files be timestamped?
	 */
	timestampFiles: boolean

	/**
	 * Array of verbosity levels for different channels
	 */
	VerbosityLevels: Array<IVerbosityLevel>
}
