import { type EmbedRequestParams, type EmbedSharedParams } from '../types';
import { type DaemonMessageOptions } from '../utils';

/**
 * Embedded peripheral API base service
 */
export class BaseEmbedService {

	/**
	 * Construct embedded peripheral request object
	 * @param {string} mType Message type
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {EmbedRequestParams | null} params Request specific parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	protected static buildOptionsWithRequest(mType: string, shared: EmbedSharedParams, params: EmbedRequestParams | null = null, options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: mType,
			data: {
				req: {
					nAdr: shared.addr,
					hwpId: shared.hwpid ?? 65535,
					param: params ?? {},
				},
				returnVerbose: shared.returnVerbose ?? true,
			},
		};
		return options;
	}
}
