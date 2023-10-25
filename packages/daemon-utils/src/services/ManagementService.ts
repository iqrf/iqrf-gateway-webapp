import { type DaemonMode, ManagementMessages } from '../enums';
import type { DaemonMessageOptions } from '../utils';

/**
 * Daemon Management API service
 */
export class ManagementService {

	/**
	 * Schedules Daemon shutdown
	 * @param {number} time Time to shutdown
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static exit(time: number, options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: ManagementMessages.Exit,
			data: {
				req: {
					timeToExit: time,
				},
				returnVerbose: true,
			},
		};
		return options;
	}

	/**
	 * Fetches current Daemon mode
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static getMode(options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: ManagementMessages.Mode,
			data: {
				req: {
					operMode: '',
				},
				returnVerbose: true,
			},
		};
		return options;
	}

	/**
	 * Sets new Daemon mode
	 * @param {DaemonMode} mode Daemon mode to set
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static setMode(mode: DaemonMode, options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: ManagementMessages.Mode,
			data: {
				req: {
					operMode: mode,
				},
				returnVerbose: true,
			},
		};
		return options;
	}

	/**
	 * Reloads coordinator
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static reloadCoordinator(options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: ManagementMessages.ReloadCoordinator,
			data: {
				returnVerbose: true,
			},
		};
		return options;
	}

	/**
	 * Updates IQRF repository cache
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static updateCache(options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: ManagementMessages.UpdateCache,
			data: {
				returnVerbose: true,
			},
		};
		return options;
	}

	/**
	 * Fetches Daemon version
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static getVersion(options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: ManagementMessages.Version,
			data: {
				returnVerbose: true,
			},
		};
		return options;
	}
}
