import { EmbedOsMessages } from '../../enums';
import {
	type EmbedSharedParams,
} from '../../types';
import {
	type BatchParams,
	type BatchParamsRaw,
	type BatchRequest,
	type BatchRequestRaw,
	type EmbedTrConfigByteParams,
	type EmbedTrConfigParams,
	type IndicateParams,
	type LoadCodeParams,
	type SelectiveBatchParams,
	type SelectiveBatchParamsRaw,
	type SetSecurityParams,
	type SleepParams,
	type TestRfSignalParams,
} from '../../types/embed';
import { type DaemonMessageOptions } from '../../utils';
import { BaseEmbedService } from '../BaseEmbedService';

/**
 * Embedded OS API service
 */
export class OsService extends BaseEmbedService {

	/**
	 * Execute batch of requests
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {BatchParams} params Batch request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static batch(shared: EmbedSharedParams, params: BatchParams, options: DaemonMessageOptions): DaemonMessageOptions {
		const data: BatchParamsRaw = {
			requests: params.requests.map((item: BatchRequest) => this.convertBatch(item)),
		};
		return this.buildOptionsWithRequest(
			EmbedOsMessages.Batch,
			shared,
			data,
			options,
		);
	}

	/**
	 * Perform factory reset
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static factorySettings(shared: EmbedSharedParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedOsMessages.FactorySettings,
			shared,
			null,
			options,
		);
	}

	/**
	 * Indicate node
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {IndicateParams} params Indicate request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static indicate(shared: EmbedSharedParams, params: IndicateParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedOsMessages.Indicate,
			shared,
			params,
			options,
		);
	}

	/**
	 * Load code to flash
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {LoadCodeParams} params LoadCode request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static loadCode(shared: EmbedSharedParams, params: LoadCodeParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedOsMessages.LoadCode,
			shared,
			params,
			options,
		);
	}

	/**
	 * Read OS data
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static read(shared: EmbedSharedParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedOsMessages.Read,
			shared,
			null,
			options,
		);
	}

	/**
	 * Read TR configuration
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static readTrConfig(shared: EmbedSharedParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedOsMessages.ReadTrConfig,
			shared,
			null,
			options,
		);
	}

	/**
	 * Reset device
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static reset(shared: EmbedSharedParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedOsMessages.Reset,
			shared,
			null,
			options,
		);
	}

	/**
	 * Restart device
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static restart(shared: EmbedSharedParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedOsMessages.Restart,
			shared,
			null,
			options,
		);
	}

	/**
	 * Run RFPGM
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static rfpgm(shared: EmbedSharedParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedOsMessages.Rfpgm,
			shared,
			null,
			options,
		);
	}

	/**
	 * Execute batch of requests on specific nodes
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {SelectiveBatchParams} params Selective batch request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static selectiveBatch(shared: EmbedSharedParams, params: SelectiveBatchParams, options: DaemonMessageOptions): DaemonMessageOptions {
		const data: SelectiveBatchParamsRaw = {
			requests: params.requests.map((item: BatchRequest) => this.convertBatch(item)),
			selectedNodes: params.selectedNodes,
		};
		return this.buildOptionsWithRequest(
			EmbedOsMessages.SelectiveBatch,
			shared,
			data,
			options,
		);
	}

	/**
	 * Set security password or user key
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {SetSecurityParams} params Set security request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static setSecurity(shared: EmbedSharedParams, params: SetSecurityParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedOsMessages.SetSecurity,
			shared,
			params,
			options,
		);
	}

	/**
	 * Put device into sleep mode
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {SleepParams} params Sleep request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static sleep(shared: EmbedSharedParams, params: SleepParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedOsMessages.Sleep,
			shared,
			params,
			options,
		);
	}

	/**
	 * Test RF signal
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {SleepParams} params Test RF signal request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static testRfSignal(shared: EmbedSharedParams, params: TestRfSignalParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedOsMessages.TestRfSignal,
			shared,
			params,
			options,
		);
	}

	/**
	 * Write TR configuration
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {EmbedTrConfigParams} params Write TR configuration request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static writeTrConfig(shared: EmbedSharedParams, params: EmbedTrConfigParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedOsMessages.WriteTrConfig,
			shared,
			params,
			options,
		);
	}

	/**
	 * Write TR configuration byte(s)
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {EmbedTrConfigByteParams} params Write TR configuration byte request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static writeTrConfigByte(shared: EmbedSharedParams, params: EmbedTrConfigByteParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			EmbedOsMessages.WriteTrConfigByte,
			shared,
			params,
			options,
		);
	}

	/**
	 * Convert batch request data to API suitable format
	 * @param {BatchRequest} request Batch request data
	 * @return {BatchRequestRaw} Batch request in API suitable format
	 */
	private static convertBatch(request: BatchRequest): BatchRequestRaw {
		const data = {
			pnum: request.pnum.toString(16).padStart(2, '0'),
			pcmd: request.pcmd.toString(16).padStart(2, '0'),
			hwpid: request.hwpid.toString(16).padStart(4, '0'),
		};
		if (request.rdata && request.rdata.length > 0) {
			Object.assign(data, { rdata: request.rdata.map((item: number) => item.toString(16).padStart(2, '0')).join('') });
		}
		return data;
	}
}
