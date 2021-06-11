import {MenderProtocols} from '../enums/Maintenance/Mender';

/**
 * Mender configuration interface
 */
export interface IMenderConfig {
	/**
	 * Inventory poll interval in seconds
	 */
	InventoryPollIntervalSeconds: number

	/**
	 * Retry poll interval in seconds
	 */
	RetryPollIntervalSeconds: number

	/**
	 * Server
	 */
	ServerURL: string

	/**
	 * Path to server certificate
	 */
	ServerCertificate?: string

	/**
	 * Tenant token
	 */
	TenantToken: string

	/**
	 * Update poll interval in seconds
	 */
	UpdatePollIntervalSeconds: number

	/**
	 * Client protocol
	 */
	ClientProtocol: MenderProtocols
}

/**
 * MMonit configuration interface
 */
export interface IMonitConfig {
    /**
     * Username
     */
    username: string

    /**
     * Password
     */
    password: string

    /**
     * Server and endpoint
     */
    endpoint: string
}
