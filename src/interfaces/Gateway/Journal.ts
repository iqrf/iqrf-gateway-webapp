/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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
import {StorageMethod, TimeUnit} from '@/enums/Gateway/SystemdJournal';

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
	 * Maximum number of files used by journal
	 */
	maxFiles: number

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
