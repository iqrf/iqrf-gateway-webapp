import { EmbedFrcMessages } from '../../enums';
import {
	type EmbedSharedParams,
} from '../../types';
import {
	type EmbedFrcSetParams,
	type EmbedFrcSetParamsRaw,
	type FrcSendParams,
	type FrcSendSelectiveParams,
} from '../../types/embed';
import { type DaemonMessageOptions } from '../../utils';
import { BaseEmbedService } from '../BaseEmbedService';

/**
 * Embedded FRC API service
 */
export class FrcService extends BaseEmbedService {

	/**
	 * Get extra FRC data that did not fit in buffer
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static extraResult(shared: EmbedSharedParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedFrcMessages.ExtraResult,
			shared,
			null,
			options,
		);
	}

	/**
	 * Send FRC request
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {FrcSendParams} params Send FRC request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static send(shared: EmbedSharedParams, params: FrcSendParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedFrcMessages.Send,
			shared,
			params,
			options,
		);
	}

	/**
	 * Send FRC request to selected nodes
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {FrcSendSelectiveParams} params Send selective FRC request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static sendSelective(shared: EmbedSharedParams, params: FrcSendSelectiveParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedFrcMessages.SendSelective,
			shared,
			params,
			options,
		);
	}

	/**
	 * Set FRC parameters
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {EmbedFrcSetParams} params Set FRC params request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static setParams(shared: EmbedSharedParams, params: EmbedFrcSetParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedFrcMessages.SetParams,
			shared,
			this.convertFrcParams(params),
			options,
		);
	}

	/**
	 * Convert user-friendly FRC parameters to single value
	 * @param {EmbedFrcSetParams} params FRC parameters
	 * @return {EmbedFrcSetParamsRaw} Convert FRC params
	 */
	private static convertFrcParams(params: EmbedFrcSetParams): EmbedFrcSetParamsRaw {
		return {
			frcResponseTime: params.frcResponseTime | (Number(params.offlineFrc) << 3),
		};
	}
}
