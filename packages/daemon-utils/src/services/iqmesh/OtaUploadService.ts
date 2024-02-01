import { IqmeshServiceMessages } from '../../enums';
import { type IqmeshSharedParams, type IqmeshOtaUploadParams } from '../../types';
import { type DaemonMessageOptions } from '../../utils';

import { BaseIqmeshService } from './BaseIqmeshService';

/**
 * IQMESH OTA API service
 */
export class OtaUploadService extends BaseIqmeshService {

	/**
	 * OTA upload
	 * @param {IqmeshSharedParams} shared Shared IQMESH request parameters
	 * @param {IqmeshOtaUploadParams} params OTA upload request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static upload(shared: IqmeshSharedParams, params: IqmeshOtaUploadParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			IqmeshServiceMessages.OtaUpload,
			shared,
			params,
			options,
		);
	}
}
