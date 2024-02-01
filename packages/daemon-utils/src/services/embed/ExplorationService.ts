import { EmbedExplorationMessages } from '../../enums';
import { type EmbedSharedParams, type PeripheralInformationParams } from '../../types';
import { type DaemonMessageOptions } from '../../utils';
import { BaseEmbedService } from '../BaseEmbedService';

/**
 * Embedded exploration API service
 */
export class ExplorationService extends BaseEmbedService {

	/**
	 * Perform peripheral exploration
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static enumerate(shared: EmbedSharedParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedExplorationMessages.Enumerate,
			shared,
			null,
			options,
		);
	}

	/**
	 * Get information about more peripherals
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {PeripheralInformationParams} params More peripheral information request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static morePeripheralsInformation(shared: EmbedSharedParams, params: PeripheralInformationParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedExplorationMessages.MorePeripheralsInformation,
			shared,
			params,
			options,
		);
	}

	/**
	 * Get information abotu peripheral
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {PeripheralInformationParams} params Peripheral information request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static peripheralInformation(shared: EmbedSharedParams, params: PeripheralInformationParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedExplorationMessages.PeripheralInformation,
			shared,
			params,
			options,
		);
	}
}
