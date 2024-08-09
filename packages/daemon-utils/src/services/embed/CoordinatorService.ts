import { EmbedCoordinatorMessages, GenericMessages } from '../../enums';
import {
	type AddrMidPairParams,
	type EmbedBackupParams,
	type EmbedSharedParams,
} from '../../types';
import {
	type AuthorizeBondParams,
	type CoordinatorRestoreParams,
	type DiscoveryParams,
	type EmbedBondNodeParams,
	type EmbedSetDpaParams,
	type EmbedSetHopsParams,
	type EmbedSmartConnectParams,
} from '../../types/embed';
import { type DaemonMessageOptions } from '../../utils';
import { BaseEmbedService } from '../BaseEmbedService';

/**
 * Embedded Coordinator API service
 */
export class CoordinatorService extends BaseEmbedService {

	/**
	 * Get addressing information
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static addrInfo(shared: EmbedSharedParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedCoordinatorMessages.AddrInfo,
			shared,
			null,
			options,
		);
	}

	/**
	 * Authorize bond
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {AuthorizeBondParams} params Authorize bond request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static authorizeBond(shared: EmbedSharedParams, params: AuthorizeBondParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedCoordinatorMessages.AuthorizeBond,
			shared,
			params,
			options,
		);
	}

	/**
	 * Backup coordinator data
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {AuthorizeBondParams} params Backup request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static backup(shared: EmbedSharedParams, params: EmbedBackupParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedCoordinatorMessages.Backup,
			shared,
			params,
			options,
		);
	}

	/**
	 * Bond IQUIP tool the network
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static bondIquip(options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: GenericMessages.Raw,
			data: {
				req: {
					rData: '00.00.00.04.ff.ff.f0.00',
				},
				returnVerbose: true,
				timeout: 11_000,
			},
		};
		return options;
	}

	/**
	 * Bond new node device
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {EmbedBondNodeParams} params Bond node request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static bondNode(shared: EmbedSharedParams, params: EmbedBondNodeParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedCoordinatorMessages.BondNode,
			shared,
			params,
			options,
		);
	}

	/**
	 * Get bonded devices
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static bondedDevices(shared: EmbedSharedParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedCoordinatorMessages.BondedDevices,
			shared,
			null,
			options,
		);
	}

	/**
	 * Clear all bonds
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static clearAllBonds(shared: EmbedSharedParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedCoordinatorMessages.ClearAllBonds,
			shared,
			null,
			options,
		);
	}

	/**
	 * Get discovered devices
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static discoveredDevices(shared: EmbedSharedParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedCoordinatorMessages.DiscoveredDevices,
			shared,
			null,
			options,
		);
	}

	/**
	 * Run discovery
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {DiscoveryParams} params Discovery request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static discovery(shared: EmbedSharedParams, params: DiscoveryParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedCoordinatorMessages.Discovery,
			shared,
			params,
			options,
		);
	}

	/**
	 * Restore coordinator data
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {DiscoveryParams} params Discovery request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static restore(shared: EmbedSharedParams, params: CoordinatorRestoreParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedCoordinatorMessages.Restore,
			shared,
			params,
			options,
		);
	}

	/**
	 * Set DPA param
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {EmbedSetDpaParams} params Set DPA param request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static setDpaParam(shared: EmbedSharedParams, params: EmbedSetDpaParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedCoordinatorMessages.SetDpaParams,
			shared,
			params,
			options,
		);
	}

	/**
	 * Set request and response hops
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {EmbedSetHopsParams} params Set hops request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static setHops(shared: EmbedSharedParams, params: EmbedSetHopsParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedCoordinatorMessages.SetHops,
			shared,
			params,
			options,
		);
	}

	/**
	 * Set device module ID
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {EmbedSetHopsParams} params Set MID request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static setMid(shared: EmbedSharedParams, params: AddrMidPairParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedCoordinatorMessages.SetMid,
			shared,
			params,
			options,
		);
	}

	/**
	 * Smart connect
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {EmbedSmartConnectParams} params SmartConnect request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static smartConnect(shared: EmbedSharedParams, params: EmbedSmartConnectParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedCoordinatorMessages.SmartConnect,
			shared,
			params,
			options,
		);
	}
}
