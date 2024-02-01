import { EmbedSpiMessages } from '../../enums';
import { type EmbedSharedParams, type WriteReadParams } from '../../types';
import { type DaemonMessageOptions } from '../../utils';
import { BaseEmbedService } from '../BaseEmbedService';

/**
 * Embedded SPI API service
 */
export class SpiService extends BaseEmbedService {

	/**
	 * Write to and read from SPI
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static writeRead(shared: EmbedSharedParams, params: WriteReadParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedSpiMessages.WriteRead,
			shared,
			params,
			options,
		);
	}
}
