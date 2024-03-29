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
 * IPv6 configuration method enum
 */
export enum Ipv6Method {
	/// SLAAC
	AUTO = 'auto',
	/// DHCPv6 only
	DHCP = 'dhcp',
	/// Link-local
	LINK_LOCAL = 'link-local',
	/// Shared with other computers
	SHARED = 'shared',
	/// Manual configuration
	MANUAL = 'manual',
	/// Ignore
	IGNORE = 'ignore',
	/// Disabled
	DISABLED = 'disabled',
}

/**
 * IPv4 configuration method enum
 */
export enum Ipv4Method {
	/// DHCPv4
	AUTO = 'auto',
	/// Link-local
	LINK_LOCAL = 'link-local',
	/// Shared with other computers
	SHARED = 'shared',
	/// Manual configuration
	MANUAL = 'manual',
	/// Disabled
	DISABLED = 'disabled',
}
