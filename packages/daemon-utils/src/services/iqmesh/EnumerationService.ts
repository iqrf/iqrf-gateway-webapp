import { IqmeshServiceMessages } from '../../enums';
import { type IqmeshEnumerateParams, type IqmeshSharedParams } from '../../types';
import { type DaemonMessageOptions } from '../../utils';

import { BaseIqmeshService } from './BaseIqmeshService';

/**
 * IQMESH Enumeration API service
 */
export class EnumerationService extends BaseIqmeshService {

	/**
	 * Read TR configuration
	 * @param {IqmeshSharedParams} shared Shared IQMESH request parameters
	 * @param {IqmeshEnumerateParams} params IQMESH Enumerate request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static enumerate(shared: IqmeshSharedParams, params: IqmeshEnumerateParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			IqmeshServiceMessages.ReadTrConfig,
			shared,
			params,
			options,
		);
	}
}
