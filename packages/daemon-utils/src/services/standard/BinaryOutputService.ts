
import { StandardBinaryOutputMessages } from '../../enums';
import { type EmbedSharedParams, type SetOutputParams } from '../../types';
import { type DaemonMessageOptions } from '../../utils';
import { BaseEmbedService } from '../BaseEmbedService';

/**
 * Standard BinaryOutput API service
 */
export class BinaryOutputService extends BaseEmbedService {

	/**
	 * Enumerate binary outputs
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static enumerate(shared: EmbedSharedParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			StandardBinaryOutputMessages.Enumerate,
			shared,
			null,
			options,
		);
	}

	/**
	 * Set binary outputs
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {SetOutputParams} params SetOutput request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static setOutput(shared: EmbedSharedParams, params: SetOutputParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			StandardBinaryOutputMessages.SetOutput,
			shared,
			params,
			options,
		);
	}
}
