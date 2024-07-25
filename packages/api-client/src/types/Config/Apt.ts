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
 * Apt configuration with raw keys
 */
export interface AptConfigRaw {
	/// Unnecessary package removal interval
	'APT::Periodic::AutocleanInterval': string,
	/// Enable automatic upgrades
	'APT::Periodic::Enable'?: string,
	/// Package upgrade interval
	'APT::Periodic::Unattended-Upgrade': string
	///Package list update interval
	'APT::Periodic::Update-Package-Lists': string,
	/// Reboot on kernel updates
	'Unattended-Upgrade::Automatic-Reboot': string,
}

/**
 * Apt configuration
 */
export interface AptConfig {
	/// Enable automatic upgrades
	enabled: boolean;
	/// Package list update interval
	packageListUpdateInterval: number;
	/// Unnecessary package removal interval
	packageRemovalInterval: number;
	/// Package update interval
	packageUpdateInterval: number;
	/// Reboot on kernel updates
	rebootOnKernelUpdate: boolean;
}
