import { GenericMessages } from '../enums';
import { type DaemonMessageOptions } from '../utils';

/**
 * Daemon Generic API service
 */
export class GenericService {

	/**
	 * Sends raw DPA packet
	 * @param {string} packet DPA packet
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static raw(packet: string, options: DaemonMessageOptions): DaemonMessageOptions {
		options.request = {
			mType: GenericMessages.Raw,
			data: {
				req: {
					rData: packet,
				},
				returnVerbose: true,
			},
		};
		return options;
	}

}
