import store from '../store';
import { WebSocketOptions } from '../store/modules/webSocketClient.module';

/**
 * Version service
 */
class VersionService {

	/**
	 * Retrieves IQRF Gateway Daemon version
	 * @param options WebSocket request options
	 */
	getVersion(options: WebSocketOptions): Promise<string> {
		options.request = {
			'mType': 'mngDaemon_Version',
			'data': {
				'returnVerbose': true,
			},
		};
		return store.dispatch('sendRequest', options);
	}
}

export default new VersionService();
