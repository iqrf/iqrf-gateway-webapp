import { IqmeshServiceMessages } from '../../enums';
import { type IqmeshFrcResponseTimeParams, type IqmeshSharedParams, type IqmeshTestRfSignalParams } from '../../types';
import { type DaemonMessageOptions } from '../../utils';

import { BaseIqmeshService } from './BaseIqmeshService';

/**
 * IQMESH Maintenance API service
 */
export class MaintenanceService extends BaseIqmeshService {

	/**
	 * Resolve internally duplicated addresses
	 * @param {IqmeshSharedParams} shared Shared IQMESH request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static duplicatedAddresses(shared: IqmeshSharedParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			IqmeshServiceMessages.MaintenanceDuplicatedAddresses,
			shared,
			null,
			options,
		);
	}

	/**
	 * Determine best FRC response time for specific FRC command
	 * @param {IqmeshSharedParams} shared Shared IQMESH request parameters
	 * @param {IqmeshFrcResponseTimeParams} params FRC response time request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static frcResponseTime(shared: IqmeshSharedParams, params: IqmeshFrcResponseTimeParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			IqmeshServiceMessages.MaintenanceFrcResponseTime,
			shared,
			params,
			options,
		);
	}

	/**
	 * Resolve inconsistent MIDs in coordinator
	 * @param {IqmeshSharedParams} shared Shared IQMESH request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static inconsistentMids(shared: IqmeshSharedParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			IqmeshServiceMessages.MaintenanceInconsistentMids,
			shared,
			null,
			options,
		);
	}

	/**
	 * Test RF signal for coordinator and the whole network
	 * @param {IqmeshSharedParams} shared Shared IQMESH request parameters
	 * @param {IqmeshFrcResponseTimeParams} params FRC response time request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static testRf(shared: IqmeshSharedParams, params: IqmeshTestRfSignalParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			IqmeshServiceMessages.MaintenanceTestRf,
			shared,
			params,
			options,
		);
	}

	/**
	 * Resolve unused prebonded nodes
	 * @param {IqmeshSharedParams} shared Shared IQMESH request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static unusedPrebondedNodes(shared: IqmeshSharedParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			IqmeshServiceMessages.MaintenanceUselessPrebondedNodes,
			shared,
			null,
			options,
		);
	}
}
