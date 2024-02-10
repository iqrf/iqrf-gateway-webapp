import { SensorDataMessages } from '../enums';
import { type SensorDataConfig } from '../types';
import { type DaemonMessageOptions } from '../utils';

/**
 * IQMESH SensorData API service
 */
export class SensorDataService {

	/**
	 * Fetch configuration
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static getConfig(options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: SensorDataMessages.GetConfig,
			data: {
				req: {},
				returnVerbose: true,
			},
		};
		return options;
	}

	/**
	 * Invoke service worker
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static invoke(options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: SensorDataMessages.Invoke,
			data: {
				req: {},
				returnVerbose: true,
			},
		};
		return options;
	}

	/**
	 * Set configuration
	 * @param {SensorDataConfig} config
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static setConfig(config: SensorDataConfig, options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: SensorDataMessages.SetConfig,
			data: {
				req: {
					autoRun: config.autoRun,
					asyncReports: config.asyncReports,
					period: config.period,
					retryPeriod: config.retryPeriod,
					messagingList: config.messagingList,
				},
				returnVerbose: true,
			},
		};
		return options;
	}

	/**
	 * Start service worker
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static start(options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: SensorDataMessages.Start,
			data: {
				req: {},
				returnVerbose: true,
			},
		};
		return options;
	}

	/**
	 * Get worker status
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static status(options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: SensorDataMessages.Status,
			data: {
				req: {},
				returnVerbose: true,
			},
		};
		return options;
	}

	/**
	 * Stop service worker
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static stop(options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: SensorDataMessages.Stop,
			data: {
				req: {},
				returnVerbose: true,
			},
		};
		return options;
	}
}
