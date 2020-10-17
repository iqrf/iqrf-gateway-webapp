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
	controller: string
	daemon: string
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
 * GatewayInfo interface
 */
export interface IGatewayInfo {
	board: string
	diskUsages: Array<DiskInfo>
	gwId: string
	hostname: string
	interfaces: Array<NetworkInterface>
	memoryUsage: MemoryInfo
	pixla: string
	swapUsage: SwapInfo
	versions: VersionsInfo
}