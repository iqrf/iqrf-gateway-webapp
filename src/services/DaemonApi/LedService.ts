import store from '../../store';
import {WebSocketOptions} from '../../store/modules/webSocketClient.module';

/**
 * EmbedLED service
 */
class LedService {

	/**
	 * Sends Daemon API request to flash red LED
	 * @param address Device address
	 * @param options WebSocket request options
	 * @return Message ID
	 */
	pulseLedr(address: number, options: WebSocketOptions): Promise<string> {
		options.request = {
			'mType': 'iqrfEmbedLedr_Pulse',
			'data': {
				'req': {
					'nAdr': address,
					'param': {},
				},
				'returnVerbose': true,
			},
		};
		return store.dispatch('sendRequest', options);
	}
}

export default new LedService();
