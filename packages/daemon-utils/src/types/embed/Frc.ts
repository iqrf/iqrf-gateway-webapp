import { type FrcResponseTime } from '../../';

/**
 * Embedded FRC Send parameters interface
 */
export interface FrcSendParams {
	/// FRC command
	frcCommand: number;
	/// Optional user data for FRC commands
	userData: number[];
}

/**
 * Embedded FRC Send Selective parameters interface
 */
export interface FrcSendSelectiveParams extends FrcSendParams {
	/// Selected node addresses
	selectedNodes: [];
}

/**
 * Embedded FRC Set parameters interface
 */
export interface EmbedFrcSetParams {
	/// FRC response time
	frcResponseTime: FrcResponseTime;
	/// Perform offline FRC
	offlineFrc: boolean;
}

/**
 * Embedded FRC Set raw parameters interface
 */
export interface EmbedFrcSetParamsRaw {
	/// FRC response time
	frcResponseTime: number;
}
