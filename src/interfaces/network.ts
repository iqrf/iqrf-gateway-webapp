/**
 * Network connection interface
 */
export interface NetworkConnection {
	interfaceName: string
	name: string
	type: string
	uuid: string
}

/**
 * Network interface interace
 */
export interface NetworkInterface {
	connectionName: string
	name: string
	state: string
	type: string
}

export interface IConnection {
	autoConnect: IConnectionAutoConnect
	name?: string
	uuid?: string
	type?: string
	interface?: string
	ipv4: IConnectionIPv4
	ipv6: IConnectionIPv6
	wifi?: IConnectionWifi
}

export interface IConnectionAutoConnect {
	enabled: boolean,
	priority: number,
	retries: number
}

export interface IConnectionCurrent {
	ipv4: IConnectionIPv4
	ipv6: IConnectionIPv6
}

export interface IConnectionIPv4 {
	addresses: Array<IConnectionIPv4Addrs>
	dns: Array<IConnectionIPDns>
	gateway: string|null
	method: string
	current?: IConnectionIPv4
}

export interface IConnectionIPv6 {
	addresses: Array<IConnectionIPv6Addrs>
	dns: Array<IConnectionIPDns>
	gateway: string|null
	method: string
	current?: IConnectionIPv6
}

export interface IConnectionIPv4Addrs {
	address: string
	mask: string
	prefix?: number
}

export interface IConnectionIPv6Addrs {
	address: string
	prefix: number
}

export interface IConnectionIPDns {
	address: string
}

export interface IConnectionWifi {
	type: string
	psk: string
	leap: IWifiLeap
	wep: IWifiWep
}

export interface IAccessPoint {
	inUse: boolean
	bssid: string
	ssid: string
	mode: string
	channel: number
	rate: string
	signal: number
	security: string
	uuid?: string
	interfaceName: string|null
}

/**
 * Cisco LEAP security interface
 */
export interface IWifiLeap {
	username: string
	password: string
}

/**
 * WEP security interface
 */
export interface IWifiWep {
	type: string
	index: number
	keys: Array<string>
}
