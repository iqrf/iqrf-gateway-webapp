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
	 * Mender server 
	 */
	ServerURL: string

	/**
	 * Mender token
	 */
	TenantToken: string

	/**
	 * Update poll interval in seconds
	 */
	UpdatePollIntervalSeconds: number
}
