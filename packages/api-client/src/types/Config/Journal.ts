/**
 * Copyright 2023-2024 MICRORISC s.r.o.
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

/**
 * Journal persistence options
 */
export enum JournalPersistence {
	/// Filesystem persistence
	Persistent = 'persistent',
	/// In-memory persistence
	Volatile = 'volatile',
}

/**
 * Journal time unit options
 */
export enum JournalTimeUnit {
	/// Days
	Days = 'day',
	/// Hours
	Hours = 'h',
	/// Minutes
	Minutes = 'm',
	/// Months
	Months = 'month',
	/// Seconds
	Seconds = 's',
	/// Weeks
	Weeks = 'week',
	/// Years
	Years = 'year',
}

/**
 * Journal size-based rotation
 */
export interface JournalSizeRotation {
	/// Maximum log file size
	maxFileSize: number;
}

/**
 * Time-based log rotation systemd journal configuration option interface
 */
export interface JournalTimeRotation {
	/// Unit count
	count: number;
	/// Time unit
	unit: JournalTimeUnit;
}

/**
 * Journal configuration
 */
export interface JournalConfig {
	/// Forward systemd journal to syslog
	forwardToSyslog: boolean;
	/// Maximum disk space usable by journal
	maxDiskSize: number;
	/// Maximum number of files used by journal
	maxFiles: number;
	/// Journal record persistence
	persistence: JournalPersistence;
	///Size-based log rotation
	sizeRotation: JournalSizeRotation;
	/// Time-based log rotation
	timeRotation: JournalTimeRotation;
}
