/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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
 * Monit check interface
 */
export interface MonitCheck extends MonitCheckEnablement {
	/// Check content
	content: string
}

/**
 * Monit check interface
 */
export interface MonitCheckEnablement {
	/// Check name
	name: string
	/// Check enablement
	enabled: boolean
}

/**
 * M/Monit credentials interface
 */
export interface MMonitCredentials {
	/// M/Monit username
	username: string
	/// M/Monit password
	password: string
}

/**
 * M/Monit configuration interface
 */
export interface MMonitConfig {
	/// M/Monit connection enablement
	enabled: boolean
	/// M/Monit server address
	server: string
	/// M/Monit credentials
	credentials: MMonitCredentials
}

/**
 * MMonit configuration interface
 */
export interface IMonitConfig {
	/// Monit check enablement
	checks: MonitCheckEnablement[]
	/// M/Monit connection configuration
	mmonit: MMonitConfig
}
