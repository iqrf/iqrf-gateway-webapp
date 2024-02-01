import { StandardSensorMessages } from '../../enums';
import { type EmbedSharedParams, type ReadSensorsFrcParams, type ReadSensorsParams } from '../../types';
import { type DaemonMessageOptions } from '../../utils';
import { BaseEmbedService } from '../BaseEmbedService';

/**
 * Standard Sensor API service
 */
export class SensorService extends BaseEmbedService {

	/**
	 * Enumerate sensors
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static enumerate(shared: EmbedSharedParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			StandardSensorMessages.Enumerate,
			shared,
			null,
			options,
		);
	}

	/**
	 * Read sensors with types
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {ReadSensorsParams} params Read sensors request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static readSensorsWithTypes(shared: EmbedSharedParams, params: ReadSensorsParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			StandardSensorMessages.ReadSensorsWithTypes,
			shared,
			params,
			options,
		);
	}

	/**
	 * Read sensors using FRC
	 * @param {EmbedSharedParams} shared Shared request parameters
	 * @param {ReadSensorsFrcParams} params Read sensors FRC request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static readFrc(shared: EmbedSharedParams, params: ReadSensorsFrcParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			StandardSensorMessages.ReadSensorsWithTypes,
			shared,
			params,
			options,
		);
	}
}
