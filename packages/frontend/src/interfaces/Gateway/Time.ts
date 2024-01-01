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

export interface ITime {
	/**
	 * Timezone name
	 */
	zoneName: string

	/**
	 * Timezone abbreviation
	 */
	abbreviation: string

	/**
	 * GMT offset code
	 */
	gmtOffset: string

	/**
	 * GMT offset in seconds
	 */
	gmtOffsetSec: number

	/**
	 * Formatted timezone string
	 */
	formattedZone: string

	/**
	 * UTC unix timestamp
	 */
	utcTimestamp: number

	/**
	 * Local unix timestamp
	 */
	localTimestamp: number

	/**
	 * Formatted time string
	 */
	formattedTime: string

	/**
	 * Is NTP used to synchronize time?
	 */
	ntpSync: boolean

	/**
	 * NTP servers
	 */
	ntpServers: Array<string>
}

export interface ITimeSet {
	/**
	 * Synchronize using NTP?
	 */
	ntpSync: boolean

	/**
	 * Timezone to set
	 */
	zoneName?: string

	/**
	 * Time to set if not synchronized
	 */
	datetime?: string

	/**
	 * NTP servers
	 */
	ntpServers?: Array<string>
}
