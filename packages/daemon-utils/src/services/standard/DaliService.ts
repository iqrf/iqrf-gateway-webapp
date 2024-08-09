import { StandardDaliMessages } from '../../enums';
import {
	type EmbedSharedParams,
} from '../../types';
import {
	type SendDaliCommandsParams,
	type SendDaliFrcCommandParams,
} from '../../types/standard';
import { type DaemonMessageOptions } from '../../utils';
import { BaseEmbedService } from '../BaseEmbedService';

/**
 * Standard DALI API service
 */
export class DaliService extends BaseEmbedService {

	/**
	 * Send DALI commands
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {SendDaliCommandsParams} params SendCommands request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static sendCommands(shared: EmbedSharedParams, params: SendDaliCommandsParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			StandardDaliMessages.SendCommands,
			shared,
			params,
			options,
		);
	}

	/**
	 * Send DALI commands and receive answers asynchronously
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {SendDaliCommandsParams} params SendCommands request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static sendCommandsAsync(shared: EmbedSharedParams, params: SendDaliCommandsParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			StandardDaliMessages.SendCommands,
			shared,
			params,
			options,
		);
	}

	/**
	 * Send DALI commands using FRC
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {SendDaliFrcCommandParams} params SendCommands FRC request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static sendFrc(shared: EmbedSharedParams, params: SendDaliFrcCommandParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			StandardDaliMessages.Frc,
			shared,
			params,
			options,
		);
	}
}
