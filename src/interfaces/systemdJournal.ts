import {StorageMethod, TimeUnit} from '../enums/gateway/SystemdJournal';

/**
 * Systemd journal configuration interface
 */
export interface ISystemdJournal {

	/**
	 * Forward systemd journal to syslog
	 */
	forwardToSyslog: boolean

	/**
	 * Journal storage method
	 */
	persistence: StorageMethod

	/**
	 * Maximum disk space usable by journal
	 */
	maxDiskSize: number

	/**
	 * Size-based log rotation
	 */
	sizeRotation: ISystemdJournalSizeRotation

	/**
	 * Time-based log rotation
	 */
	timeRotation: ISystemdJournalTimeRotation
}

/**
 * Size-base log rotation systemd journal configuration option interface
 */
export interface ISystemdJournalSizeRotation {

	/**
	 * Maximum number of rotated log files to keep
	 */
	maxFiles: number

	/**
	 * Maximum log file size
	 */
	maxFileSize: number
}

/**
 * Time-based log rotation systemd journal configuration option interface
 */
export interface ISystemdJournalTimeRotation {

	/**
	 * Time unit
	 */
	unit: TimeUnit

	/**
	 * Unit count
	 */
	count: number
}
