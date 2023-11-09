/**
 * Copyright 2023 MICRORISC s.r.o.
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
 * Monit check with definition
 */
export interface MonitCheckWithDefinition {
	/// Check definition
	content: string;
}

/**
 * Monit check
 */
export interface MonitCheck {
	/// Check status
	enabled: boolean;
	/// Check name
	name: string;
}

/**
 * MMonit client credentials
 */
export interface MMonitCredentials {
	/// Password
	password: string;
	/// Username
	username: string;
}

/**
 * MMonit client configuration
 */
export interface MMonitConfig {
	/// Credentials
	credentials: MMonitCredentials;
	/// Status
	enabled: boolean;
	/// Server address
	server: string;
}

/**
 * Monit configuration
 */
export interface MonitConfig {
	/// Monit checks
	checks: MonitCheck[]
	/// MMonit client configuration
	mmonit: MMonitConfig
}
