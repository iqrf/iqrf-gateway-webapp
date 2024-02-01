import { EmbedThermometerMessages } from '../../enums';
import { type EmbedSharedParams } from '../../types';
import { type DaemonMessageOptions } from '../../utils';
import { BaseEmbedService } from '../BaseEmbedService';

/**
 * Embedded Thermometer API service
 */
export class ThermometerService extends BaseEmbedService {

	/**
	 * Read thermometer value
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static read(shared: EmbedSharedParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedThermometerMessages.Read,
			shared,
			null,
			options,
		);
	}
}
