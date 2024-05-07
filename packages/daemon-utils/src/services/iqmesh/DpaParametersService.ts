import { IqmeshServiceMessages } from '../../enums';
import {
	type IqmeshDpaHopsParams,
	type IqmeshDpaValueParams,
	type IqmeshFrcParams,
	type IqmeshSharedParams,
} from '../../types';
import { type DaemonMessageOptions } from '../../utils';

import { BaseIqmeshService } from './BaseIqmeshService';

/**
 * IQMESH DPA parameters API service
 */
export class DpaParametersService extends BaseIqmeshService {

	/**
	 * Get or set DPA hops
	 * @param {IqmeshSharedParams} shared Shared IQMESH request parameters
	 * @param {IqmeshDpaHopsParams} params DPA hops request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static dpaHops(shared: IqmeshSharedParams, params: IqmeshDpaHopsParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			IqmeshServiceMessages.DpaHops,
			shared,
			params,
			options,
		);
	}

	/**
	 * Get or set DPA value
	 * @param {IqmeshSharedParams} shared Shared IQMESH request parameters
	 * @param {IqmeshDpaValueParams} params DPA value request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static dpaValue(shared: IqmeshSharedParams, params: IqmeshDpaValueParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			IqmeshServiceMessages.DpaValue,
			shared,
			params,
			options,
		);
	}

	/**
	 * Get or set FRC parameters
	 * @param {IqmeshSharedParams} shared Shared IQMESH request parameters
	 * @param {IqmeshFrcParams} params FRC params request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static frcParams(shared: IqmeshSharedParams, params: IqmeshFrcParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			IqmeshServiceMessages.DpaValue,
			shared,
			params,
			options,
		);
	}
}
