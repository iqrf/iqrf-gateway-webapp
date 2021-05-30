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
