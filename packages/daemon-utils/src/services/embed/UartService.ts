import { EmbedSpiMessages, EmbedUartMessages } from '../../enums';
import { type EmbedSharedParams, type WriteReadParams } from '../../types';
import { type UartOpenParams } from '../../types/embed';
import { type DaemonMessageOptions } from '../../utils';
import { BaseEmbedService } from '../BaseEmbedService';

/**
 * Embedded UART API service
 */
export class UartService extends BaseEmbedService {

	/**
	 * Clear buffers, Write to and read from UART
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {WriteReadParams} params Write and Read request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static clearWriteRead(shared: EmbedSharedParams, params: WriteReadParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedUartMessages.ClearWriteRead,
			shared,
			params,
			options,
		);
	}

	/**
	 * Close UART
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static close(shared: EmbedSharedParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedUartMessages.Close,
			shared,
			null,
			options,
		);
	}

	/**
	 * Clear buffers, Write to and read from UART
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {UartOpenParams} params Write and Read request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static open(shared: EmbedSharedParams, params: UartOpenParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedUartMessages.Open,
			shared,
			params,
			options,
		);
	}

	/**
	 * Write to and read from UART
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {WriteReadParams} params Write and Read request parameters
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
