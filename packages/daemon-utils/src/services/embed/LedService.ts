import { EmbedLedgMessages, EmbedLedrMessages } from '../../enums';
import { type EmbedRequestParams, type EmbedSharedParams } from '../../types';
import { type DaemonMessageOptions } from '../../utils';
import { BaseEmbedService } from '../BaseEmbedService';

/**
 * Embedded LED API service
 */
export class LedService extends BaseEmbedService {

	/**
	 * Flash red LED continuously
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static redFlashing(shared: EmbedSharedParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedLedrMessages.Flashing,
			shared,
			null,
			options,
		);
	}

	/**
	 * Generate red LED pulse
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static redPulse(shared: EmbedSharedParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedLedrMessages.Pulse,
			shared,
			null,
			options,
		);
	}

	/**
	 * Set red LED on or off
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {EmbedRequestParams} params LEDR set parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static redSet(shared: EmbedSharedParams, params: EmbedRequestParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedLedrMessages.Set,
			shared,
			params,
			options,
		);
	}

	/**
	 * Set red LED off
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static redSetOff(shared: EmbedSharedParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedLedrMessages.SetOff,
			shared,
			null,
			options,
		);
	}

	/**
	 * Set red LED on
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static redSetOn(shared: EmbedSharedParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedLedrMessages.SetOn,
			shared,
			null,
			options,
		);
	}

	/**
	 * Flash green LED continuously
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static greenFlashing(shared: EmbedSharedParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedLedgMessages.Flashing,
			shared,
			null,
			options,
		);
	}

	/**
	 * Generate green LED pulse
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static greenPulse(shared: EmbedSharedParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedLedgMessages.Pulse,
			shared,
			null,
			options,
		);
	}

	/**
	 * Set green LED on or off
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {EmbedRequestParams} params LEDG set parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static greenSet(shared: EmbedSharedParams, params: EmbedRequestParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedLedgMessages.Set,
			shared,
			params,
			options,
		);
	}

	/**
	 * Set green LED off
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static greenSetOff(shared: EmbedSharedParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedLedgMessages.SetOff,
			shared,
			null,
			options,
		);
	}

	/**
	 * Set green LED off
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static greenSetOn(shared: EmbedSharedParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedLedgMessages.SetOn,
			shared,
			null,
			options,
		);
	}
}
