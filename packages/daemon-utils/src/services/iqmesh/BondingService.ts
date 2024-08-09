import { IqmeshServiceMessages } from '../../enums';
import {
	type IqmeshSharedParams,
} from '../../types';
import {
	type IqmeshAutonetworkParams,
	type IqmeshBondNodeParams,
	type IqmeshRemoveBond3Params,
	type IqmeshRemoveBondCoordinatorParams,
	type IqmeshRemoveBondParams,
	type IqmeshSmartConnectParams,
} from '../../types/iqmesh';
import { type DaemonMessageOptions } from '../../utils';

import { BaseIqmeshService } from './BaseIqmeshService';

/**
 * IQMESH Bonding API service
 */
export class BondingService extends BaseIqmeshService {

	/**
	 * Perform AutoNetwork
	 * @param {IqmeshSharedParams} shared Shared IQMESH request parameters
	 * @param {IqmeshAutonetworkParams} params IQMESH BondNode request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static autonetwork(shared: IqmeshSharedParams, params: IqmeshAutonetworkParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			IqmeshServiceMessages.Autonetwork,
			shared,
			params,
			options,
		);
	}

	/**
	 * Bond a device locally
	 * @param {IqmeshSharedParams} shared Shared IQMESH request parameters
	 * @param {IqmeshBondNodeParams} params IQMESH BondNode request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static bondNode(shared: IqmeshSharedParams, params: IqmeshBondNodeParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			IqmeshServiceMessages.BondNode,
			shared,
			params,
			options,
		);
	}

	/**
	 * Unbond a device or all devices from network
	 * @param {IqmeshSharedParams} shared Shared IQMESH request parameters
	 * @param {IqmeshRemoveBondParams} params IQMESH RemoveBond request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static removeBond(shared: IqmeshSharedParams, params: IqmeshRemoveBondParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			IqmeshServiceMessages.RemoveBond,
			shared,
			params,
			options,
		);
	}

	/**
	 * Unbond device(s) in coordinator memory only
	 * @param {IqmeshSharedParams} shared Shared IQMESH request parameters
	 * @param {IqmeshRemoveBondCoordinatorParams} params IQMESH RemoveBondCoordinator request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static removeBondCoordinator(shared: IqmeshSharedParams, params: IqmeshRemoveBondCoordinatorParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			IqmeshServiceMessages.RemoveBondCoordinator,
			shared,
			params,
			options,
		);
	}

	/**
	 * Unbond device(s) from network
	 * @param {IqmeshSharedParams} shared Shared IQMESH request parameters
	 * @param {IqmeshRemoveBond3Params} params RemoveBond service request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static removeBond3(shared: IqmeshSharedParams, params: IqmeshRemoveBond3Params, options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: IqmeshServiceMessages.RemoveBond,
			data: {
				req: {
					hwpId: params.hwpId,
					coordinatorOnly: params.coordinatorOnly,
				},
				repeat: shared.repeat ?? 1,
				returnVerbose: shared.returnVerbose ?? true,
			},
		};
		if (params.allNodes) {
			options.request.data.req = { ...options.request.data.req, ...{ allNodes: true } };
		} else {
			options.request.data.req = { ...options.request.data.req, ...{ deviceAddr: params.deviceAddr } };
		}
		return options;
	}

	/**
	 * Bond a device via IQMESH SmartConnect
	 * @param {IqmeshSharedParams} shared Shared IQMESH request parameters
	 * @param {IqmeshSmartConnectParams} params IQMESH SmartConnect request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static smartConnect(shared: IqmeshSharedParams, params: IqmeshSmartConnectParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			IqmeshServiceMessages.SmartConnect,
			shared,
			params,
			options,
		);
	}
}
