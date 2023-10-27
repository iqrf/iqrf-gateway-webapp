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
 * Current gateway time configuration
 */
export interface TimeConfig {
	/// Time zone code
	abbrevation: string;
	/// Formatted time string
	formattedTime: string;
	/// Formatted zone string
	formattedZone: string;
	/// UTC/GMT offset
	gmtOffset: string;
	/// UTC/GMT offset in seconds
	gmtOffsetSec: number;
	/// Local timestamp
	localTimestamp: number;
	/// NTP synchronization servers
	ntpServers: string[];
	/// NTP synchronization enabled
	ntpSync: boolean;
	/// UTC timestamp
	utcTimestamp: number;
	/// Time zone name
	zoneName: string;
}

/**
 * Gateway time set interface
 */
export interface TimeSet {
	/// Datetime to set ntp synchronization is disabled
	datetime?: string;
	/// NTP servers
	ntpServers?: string[];
	/// Enable NTP synchronization
	ntpSync: boolean;
	/// Timezone to set
	zoneName?: string;
}

/**
 * Gateway time zone
 */
export interface Timezone {
	/// Time zone code
	code: string;
	/// Time zone name
	name: string;
	/// UTC/GMT offset
	offset: string;
}
