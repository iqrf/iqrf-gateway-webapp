import { IqmeshServiceMessages, SensorDataServiceCommands } from '../../enums';
import { type SensorDataServiceConfig } from '../../types';
import { type DaemonMessageOptions } from '../../utils';

/**
 * IQMESH SensorData API service
 */
export class SensorDataService {

	/**
	 * Fetch service configuration
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static getConfig(options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: IqmeshServiceMessages.SensorData,
			data: {
				req: {
					command: SensorDataServiceCommands.GetConfig,
				},
				returnVerbose: true,
			},
		};
		return options;
	}

	/**
	 * Set service configuration
	 * @param {SensorDataServiceConfig} config
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static setConfig(config: SensorDataServiceConfig, options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: IqmeshServiceMessages.SensorData,
			data: {
				req: {
					command: SensorDataServiceCommands.SetConfig,
					autoRun: config.autoRun,
					asyncReports: config.asyncReports,
					period: config.period,
					messagingList: config.messagingList,
				},
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
			mType: IqmeshServiceMessages.SensorData,
			data: {
				req: {
					command: SensorDataServiceCommands.Invoke,
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
			mType: IqmeshServiceMessages.SensorData,
			data: {
				req: {
					command: SensorDataServiceCommands.Start,
				},
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
			mType: IqmeshServiceMessages.SensorData,
			data: {
				req: {
					command: SensorDataServiceCommands.Stop,
				},
				returnVerbose: true,
			},
		};
		return options;
	}
}
