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
	ssid: string
	mode: string
	bssids?: Array<string>
	security: IWifiSecurity
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

export interface IWifiSecurity {
	type: string
	psk: string
	leap: IWifiLeap
	wep: IWifiWep
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

/**
 * Wireguard VPN list entry interface
 */
export interface IWG {
	name: string
	state: string
}

/**
 * Wireguard VPN tunnel interface
 */
export interface IWGTunnel {
	name: string
	privateKey: string
	publicKey?: string
	port?: number
	ipv4: string
	ipv4Prefix: number
	ipv6: string
	ipv6Prefix: number
	peers: Array<IWGPeer>
}

/**
 * Wireguard VPN peer interface
 */
export interface IWGPeer {
	publicKey: string,
	psk: string
	keepalive: number
	endpoint: string
	port: number
	allowedIPs: IWGAllowedIPs
}

/**
 * Wireguard VPN peer allowed IPs interface
 */
export interface IWGAllowedIPs {
	ipv4: Array<IWGAllowedIP>
	ipv6: Array<IWGAllowedIP>
}

/**
 * Wireguard VPN peer allowed IP interface
 */
export interface IWGAllowedIP {
	address: string
	prefix: number
}
