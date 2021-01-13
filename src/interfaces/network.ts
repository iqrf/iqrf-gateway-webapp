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
    interfaceName: string
    name: string
    state: string
    type: string
}

export interface IConnection {
    name?: string
    uuid?: string
    type?: string
    interface?: string
    ipv4: IConnectionIPv4
    ipv6: IConnectionIPv6
}

export interface IConnectionIPv4 {
	addresses: Array<IConnectionIPv4Addrs>
	dns: Array<IConnectionIPDns>
	gateway?: string
	method: string
}

export interface IConnectionIPv6 {
    addresses: Array<IConnectionIPv6Addrs>
    dns: Array<IConnectionIPDns>
    method: string
}

export interface IConnectionIPv4Addrs {
    address: string
    mask: string
    prefix?: number
}

export interface IConnectionIPv6Addrs {
    address: string
    prefix: number
    gateway: string
}

export interface IConnectionIPDns {
    address: string
}
