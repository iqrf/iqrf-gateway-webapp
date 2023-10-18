/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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
 * GatewayInfo ip address interface
 */
export interface IpAddress {
	addresses: string
	iface: string
}

/**
 * GatewayInfo mac address interface
 */
export interface MacAddress {
	address: string
	iface: string
}

/**
 * GatewayInfo disk information interface
 */
export interface DiskInfo {
	available: string
	fsName: string
	fsType: string
	mount: string
	size: string
	usage: string
	used: string
}

export interface OsInfo {
	name: string|null
	homePage: string|null
}

/**
 * GatewayInfo memory information interface
 */
export interface MemoryInfo {
	available: string
	buffers: string
	cache: string
	free: string
	shared: string
	size: string
	usage: string
	used: string
}

/**
 * Resource usage data
 */
export interface IUsageData {
	/**
	 * Usage as percentage
	 */
	usage: string;

	/**
	 * Used size
	 */
	used: string;

	/**
	 * Total size
	 */
	size: string;
}

/**
 * GatewayInfo swap information interface
 */
export interface SwapInfo {
	free: string
	size: string
	usage: string
	used: string
}

/**
 * GatewayInfo iqrf software versions interface
 */
export interface VersionsInfo {
	cloudProvisioning: string
	controller: string
	daemon: string
	setter: string
	uploader: string
	webapp: string
}

/**
 * GatewayInfo network interfaces interface
 */
export interface NetworkInterface {
	ipAddresses: Array<string>|null
	macAddress: string|null
	name: string
}

/**
 * Brief gateway information interface
 */
export interface IGatewayBriefInfo {
	/// Board name
	board: string
}

/**
 * GatewayInfo interface
 */
export interface IGatewayInfo {
	board: string
	gwId: string|null
	gwImage: string|null
	os: OsInfo
	versions: VersionsInfo
	hostname: string
	interfaces: Array<NetworkInterface>
	diskUsages: Array<DiskInfo>
	memoryUsage: MemoryInfo
	swapUsage: SwapInfo
	uptime: string
}

/**
 * Hostnamectl interface
 */
export interface IHostname {
	hostname: string
}
