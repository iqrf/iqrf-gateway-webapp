import { type IqmeshRequestParams, type IqmeshSharedParams } from '../../types';
import { type DaemonMessageOptions } from '../../utils';

/**
 * IQMESH service API base service
 */
export class BaseIqmeshService {

	/**
	 * Construct IQMESH service request object
	 * @param {string} mType Message type
	 * @param {IqmeshSharedParams} shared Shared request parameters
	 * @param {IqmeshRequestParams | null} params Request specific parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	protected static buildOptionsWithRequest(mType: string, shared: IqmeshSharedParams, params: IqmeshRequestParams | null, options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: mType,
			data: {
				repeat: shared.repeat ?? 1,
				returnVerbose: shared.returnVerbose ?? true,
			},
		};
		if (params !== null) {
			Object.assign(options.request.data, { req: params });
		}
		return options;
	}
}
