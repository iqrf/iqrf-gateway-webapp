import { NotificationMessages } from '../enums';
import { type DaemonMessageOptions } from '../utils';

/**
 * Daemon Monitor API service
 */
export class MonitorService {

	/**
	 * Request monitor notification now
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static invoke(options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: NotificationMessages.InvokeMonitor,
			data: {
				returnVerbose: true,
			},
		};
		return options;
	}
}
