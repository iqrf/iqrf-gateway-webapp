import { IqmeshServiceMessages } from '../../enums';
import { type IqmeshNetworkParams, type IqmeshSharedParams } from '../../types';
import { type DaemonMessageOptions } from '../../utils';

import { BaseIqmeshService } from './BaseIqmeshService';

/**
 * IQMESH Network API service
 */
export class NetworkService extends BaseIqmeshService {

	/**
	 * Ping network devices to get online status
	 * @param {IqmeshSharedParams} shared Shared IQMESH request parameters
	 * @param {IqmeshNetworkParams} params IQMESH Ping request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static ping(shared: IqmeshSharedParams, params: IqmeshNetworkParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			IqmeshServiceMessages.Ping,
			shared,
			params,
			options,
		);
	}

	/**
	 * Restart network devices to get online status
	 * @param {IqmeshSharedParams} shared Shared IQMESH request parameters
	 * @param {IqmeshNetworkParams} params IQMESH Restart request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static restart(shared: IqmeshSharedParams, params: IqmeshNetworkParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			IqmeshServiceMessages.Restart,
			shared,
			params,
			options,
		);
	}
}
