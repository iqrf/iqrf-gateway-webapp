import { EmbedEeepromMessages, EmbedEepromMessages, EmbedRamMessages } from '../../enums';
import { type EmbedSharedParams, type MemoryReadParams, type MemoryWriteParams } from '../../types';
import { type DaemonMessageOptions } from '../../utils';
import { BaseEmbedService } from '../BaseEmbedService';

/**
 * Embedded EEPROM API service
 */
export class EepromService extends BaseEmbedService {

	/**
	 * Read data from EEPROM
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {MemoryReadParams} params EEPROM read parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static eepromRead(shared: EmbedSharedParams, params: MemoryReadParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedEepromMessages.Read,
			shared,
			params,
			options,
		);
	}

	/**
	 * Write data to EEPROM
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {MemoryWriteParams} params EEPROM write parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static eepromWrite(shared: EmbedSharedParams, params: MemoryWriteParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedEepromMessages.Write,
			shared,
			params,
			options,
		);
	}

	/**
	 * Read data from EEEPROM
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {MemoryReadParams} params EEEPROM read parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static eeepromRead(shared: EmbedSharedParams, params: MemoryReadParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedEeepromMessages.Read,
			shared,
			params,
			options,
		);
	}

	/**
	 * Write data to EEEPROM
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {MemoryWriteParams} params EEEPROM write parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static eeepromWrite(shared: EmbedSharedParams, params: MemoryWriteParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedEeepromMessages.Write,
			shared,
			params,
			options,
		);
	}

	/**
	 * Read data from RAM
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {MemoryReadParams} params RAM read parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static ramRead(shared: EmbedSharedParams, params: MemoryReadParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedRamMessages.Read,
			shared,
			params,
			options,
		);
	}

	/**
	 * Write data to RAM
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {MemoryWriteParams} params RAM write parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static ramWrite(shared: EmbedSharedParams, params: MemoryWriteParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedRamMessages.Write,
			shared,
			params,
			options,
		);
	}
}
