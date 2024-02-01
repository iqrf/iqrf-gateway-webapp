import { IqmeshServiceMessages } from '../../enums';
import { type IqmeshSharedParams, type IqmeshReadTrConfigParams, type IqmeshWriteTrConfigParams } from '../../types';
import { type DaemonMessageOptions } from '../../utils';

import { BaseIqmeshService } from './BaseIqmeshService';

/**
 * IQMESH TR configuration API service
 */
export class TrConfigurationService extends BaseIqmeshService {

	/**
	 * Read TR configuration
	 * @param {IqmeshSharedParams} shared Shared IQMESH request parameters
	 * @param {IqmeshReadTrConfigParams} params  IQMESH ReadTrConfig request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static read(shared: IqmeshSharedParams, params: IqmeshReadTrConfigParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			IqmeshServiceMessages.ReadTrConfig,
			shared,
			params,
			options,
		);
	}

	/**
	 * Write TR configuration
	 * @param {IqmeshSharedParams} shared Shared IQMESH request parameters
	 * @param {IqmeshWriteTrConfigParams} params IQMESH WriteTrConfig request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static write(shared: IqmeshSharedParams, params: IqmeshWriteTrConfigParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			IqmeshServiceMessages.WriteTrConfig,
			shared,
			params,
			options,
		);
	}
}
