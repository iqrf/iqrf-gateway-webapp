import { type DpaParam } from '../../enums';

/**
 * Embedded Coordinator authorize bonds parameters interface
 */
export interface AuthorizeBondParams {
	/// Module ID
	mid: number;
	/// Requested address
	reqAddr: number;
}

/**
 * Embedded Coordinator restore parameters interface
 */
export interface CoordinatorRestoreParams {
	/// Coordinator backup data
	networkData: number[];
}

/**
 * Embedded Coordinator discovery parameters interface
 */
export interface DiscoveryParams {
	/// Maximum address used in discovery
	maxAddr: number;
	/// TX power
	txPower: number;
}

/**
 * Embedded Coordinator bond node parameters interface
 */
export interface EmbedBondNodeParams {
	/// Requested address
	reqAddr: number;
}

/**
 * Embedded Coordinator set DPA param parameters interface
 */
export interface EmbedSetDpaParams {
	/// DPA value
	dpaParam: DpaParam;
}

/**
 * Embedded Coordinator set hops parameters interface
 */
export interface EmbedSetHopsParams {
	/// Hops used to deliver request
	requestHops: number;
	/// Hops used to deliver response
	responseHops: number;
}

/**
 * Embedded Coordinator smart connect parameters interface
 */
export interface EmbedSmartConnectParams {
	/// Number of FRCs used to test bonding success
	bondingTestRetries: number;
	/// Individual bonding key
	ibk: number[];
	/// Module ID
	mid: number;
	/// Requested address
	reqAddr: number;
	/// Virtual device address
	virtualDeviceAddress: number;
}
