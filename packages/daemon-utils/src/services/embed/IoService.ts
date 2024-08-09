import { EmbedIoMessages } from '../../enums';
import { type EmbedSharedParams } from '../../types';
import { type IoParams } from '../../types/embed';
import { type DaemonMessageOptions } from '../../utils';
import { BaseEmbedService } from '../BaseEmbedService';

/**
 * Embedded Coordinator API service
 */
export class IoService extends BaseEmbedService {

	/**
	 * Set IO direction
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {IoParams} params Set IO direction request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static direction(shared: EmbedSharedParams, params: IoParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedIoMessages.Direction,
			shared,
			params,
			options,
		);
	}

	/**
	 * Set IO values
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {IoParams} params Set IO values request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static set(shared: EmbedSharedParams, params: IoParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedIoMessages.Set,
			shared,
			params,
			options,
		);
	}

	/**
	 * Get IO values
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static get(shared: EmbedSharedParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedIoMessages.Get,
			shared,
			null,
			options,
		);
	}
}
