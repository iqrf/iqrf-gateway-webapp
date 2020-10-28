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
