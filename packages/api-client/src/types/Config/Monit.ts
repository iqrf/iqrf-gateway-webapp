/**
 * Copyright 2023-2023 MICRORISC s.r.o.
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
export interface MonitCheckEnablement {
	/// Check enablement
	enabled: boolean,
	/// Check name
	name: string
}

/**
 * Monit check interface
 */
export interface MonitCheck extends MonitCheckEnablement {
	/// Check content
	content: string
}

/**
 * M/Monit credentials interface
 */
export interface MMonitCredentials {
	/// M/Monit password
	password: string,
	/// M/Monit username
	username: string
}

/**
 * M/Monit configuration interface
 */
export interface MMonitConfig {
	/// M/Monit credentials
	credentials: MMonitCredentials,
	/// M/Monit connection enablement
	enabled: boolean,
	/// M/Monit server address
	server: string
}

/**
 * Monit configuration interface
 */
export interface MonitConfig {
	/// Monit check enablement
	checks: MonitCheckEnablement[]
	/// M/Monit connection configuration
	mmonit: MMonitConfig
}
