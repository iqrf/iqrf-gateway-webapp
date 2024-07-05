import { type DpaParam, type FrcResponseTime } from '../../enums/embed';
import { type IqmeshConfigAction } from '../../enums/iqmesh';

/**
 * IQMESH DpaHops parameters interface
 */
export interface IqmeshDpaHopsParams {
	/// Get or set DPA hops
	action: IqmeshConfigAction;
	/// Optional hops used to deliver request
	requestHops?: number;
	/// Optional hops used to deliver response
	responseHops?: number;
}

/**
 * IQMESH DpaValue parameters interface
 */
export interface IqmeshDpaValueParams {
	/// Get or set DPA value
	action: IqmeshConfigAction;
	/// Optional DPA value to set
	type?: DpaParam;
}

/**
 * IQMESH FRC params parameters interface
 */
export interface IqmeshFrcParams {
	/// Get or set FRC params
	action: IqmeshConfigAction;
	/// Perform offline FRC
	offlineFrc?: boolean;
	/// FRC response time
	responseTime?: FrcResponseTime;
}
