import { EmbedNodeMessages } from '../../enums';
import {
	type EmbedBackupParams,
	type EmbedSharedParams,
	type NodeRestoreParams,
	type ValidateBondsParams,
} from '../../types';
import { type DaemonMessageOptions } from '../../utils';
import { BaseEmbedService } from '../BaseEmbedService';

/**
 * Embedded Node API service
 */
export class NodeService extends BaseEmbedService {

	/**
	 * Backup Node data
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {BackupParams} params Node backup parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static backup(shared: EmbedSharedParams, params: EmbedBackupParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedNodeMessages.Backup,
			shared,
			params,
			options,
		);
	}

	/**
	 * Read node information
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static read(shared: EmbedSharedParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedNodeMessages.Read,
			shared,
			null,
			options,
		);
	}

	/**
	 * Remove bond
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static removeBond(shared: EmbedSharedParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedNodeMessages.RemoveBond,
			shared,
			null,
			options,
		);
	}

	/**
	 * Restore node data
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {NodeRestoreParams} params Node backup parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static restore(shared: EmbedSharedParams, params: NodeRestoreParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedNodeMessages.Restore,
			shared,
			params,
			options,
		);
	}

	/**
	 * Validate bonds
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {ValidateBondsParams} params Node backup parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static validateBonds(shared: EmbedSharedParams, params: ValidateBondsParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedNodeMessages.ValidateBonds,
			shared,
			params,
			options,
		);
	}

}
