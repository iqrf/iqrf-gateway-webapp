/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
import {Persistence, TimeUnit} from '@/enums/Gateway/Journal';

/**
 * Systemd journal configuration interface
 */
export interface IJournal {

	/**
	 * Forward systemd journal to syslog
	 */
	forwardToSyslog: boolean

	/**
	 * Journal storage method
	 */
	persistence: Persistence

	/**
	 * Maximum disk space usable by journal
	 */
	maxDiskSize: number

	/**
	 * Maximum number of files used by journal
	 */
	maxFiles: number

	/**
	 * Size-based log rotation
	 */
	sizeRotation: IJournalSizeRotation

	/**
	 * Time-based log rotation
	 */
	timeRotation: IJournalTimeRotation
}

/**
 * Size-base log rotation systemd journal configuration option interface
 */
export interface IJournalSizeRotation {

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
export interface IJournalTimeRotation {

	/**
	 * Time unit
	 */
	unit: TimeUnit

	/**
	 * Unit count
	 */
	count: number
}

/**
 * Journal data interface
 */
export interface IJournalData {
	/**
	 * Journal records
	 */
	records: Array<string>

	/**
	 * Cursor of the first record
	 */
	startCursor: string

	/**
	 * Cursor of the last record
	 */
	endCursor: string
}

