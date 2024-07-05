import { StandardLightMessages } from '../../enums';
import { type EmbedSharedParams } from '../../types';
import { type LightPowerParams } from '../../types/standard';
import { type DaemonMessageOptions } from '../../utils';
import { BaseEmbedService } from '../BaseEmbedService';

/**
 * Standard Light API service
 */
export class LightService extends BaseEmbedService {

	/**
	 * Decrement light power
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {LightPowerParams} params Decrement power request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static decrementPower(shared: EmbedSharedParams, params: LightPowerParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			StandardLightMessages.DecrementPower,
			shared,
			params,
			options,
		);
	}

	/**
	 * Enumerate lights
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static enumerate(shared: EmbedSharedParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			StandardLightMessages.Enumerate,
			shared,
			null,
			options,
		);
	}

	/**
	 * Increment light power
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {LightPowerParams} params Increment power request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static incrementPower(shared: EmbedSharedParams, params: LightPowerParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			StandardLightMessages.IncrementPower,
			shared,
			params,
			options,
		);
	}

	/**
	 * Set light power
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {LightPowerParams} params Set power request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static setPower(shared: EmbedSharedParams, params: LightPowerParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			StandardLightMessages.SetPower,
			shared,
			params,
			options,
		);
	}
}
