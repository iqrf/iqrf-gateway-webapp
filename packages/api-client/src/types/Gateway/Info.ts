/**
 * Copyright 2023-2025 MICRORISC s.r.o.
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
 * Information about used operating system
 */
export interface OperatingSystem {
	/// Operating system home page
	homePage: string | null;
	/// Operating system name
	name: string | null;
}

/**
 * Base usage interface
 */
export interface UsageBase {
	/// Total size
	size: string;
	/// Usage as percentage
	usage: string;
	/// Used size
	used: string;
}

/**
 * File system usage information
 */
export interface FileSystemUsage extends UsageBase {
	/// Available space
	available: string;
	/// File system name
	fsName: string;
	/// File system name
	fsType: string;
	/// Mount point
	mount: string;
}

/**
 * Memory usage
 */
export interface MemoryUsage extends UsageBase {
	/// Available memory
	available: string;
	/// Memory used for buffers
	buffers: string;
	/// Memory used for cache
	cache: string;
	/// Free memory
	free: string;
	/// Shared memory
	shared: string;
}

/**
 * Swap usage
 */
export interface SwapUsage extends UsageBase {
	/// Free swap
	free: string;
}

/**
 * Network interface information
 */
export interface NetworkInterface {
	/// Network interface IP addresses
	ipAddresses: string[]|null;
	/// Network interface MAC address
	macAddress: string|null;
	/// Network interface name
	name: string;
}

/**
 * IQRF Gateway software versions
 */
export interface SoftwareVersions {
	/// IQRF Cloud Provisioning version
	cloudProvisioning: string|null;
	/// IQRF Gateway Controller version
	controller: string|null;
	/// IQRF Gateway Daemon version
	daemon: string|null;
	/// IQRF Gateway InfluxDB Bridge version
	influxdbBridge: string|null;
	/// IQRF Gateway Setter version
	setter: string|null;
	/// IQRF Gateway Uploader version
	uploader: string|null;
	/// IQRF Gateway Webapp version
	webapp: string|null;
}

/**
 * Brief information about IQRF Gateway
 */
export interface GatewayBriefInformation {
	/// Gateway board manufacturer and model
	board: string;
	/// Gateway ID
	gwId: string | null;
}

/**
 * Information about eMMC flash memory health
 */
export interface EmmcHealth {
	/// eMMC SLC region life time estimation
	slc_region: string | null;
	/// eMMC MLC region life time estimation
	mlc_region: string | null;
	/// eMMC pre EOL info (overall status)
	status: string | null;
}

/**
 * Information about IQRF Gateway
 */
export interface GatewayInformation extends GatewayBriefInformation {
	/// Disk usages
	diskUsages: FileSystemUsage[];
	/// Gateway OS image version
	gwImage: string | null;
	/// Gateway hostname
	hostname: string;
	/// Network interfaces
	interfaces: NetworkInterface[];
	/// Memory usage
	memoryUsage: MemoryUsage;
	/// Operating system information
	os: OperatingSystem;
	/// Swap usage
	swapUsage: SwapUsage | null;
	/// Uptime
	uptime: string;
	/// IQRF Gateway SW versions
	versions: SoftwareVersions;
	/// eMMC health
	emmcHealth: EmmcHealth | null;
}
