import store from '../../store';
import { WebSocketOptions } from '../../store/modules/webSocketClient.module';

/**
 * OS service
 */
class OsService {

	/**
	 * Sends OS Read request
	 * @param address Address
	 * @param timeout Timeout in milliseconds
	 * @param message Timeout message
	 * @param callback Timeout callback
	 * @return Message ID
	 */
	sendRead(address: number, timeout: number, message: string|null = null, callback: CallableFunction = () => {return;}): Promise<string> {
		const request = {
			'mType': 'iqrfEmbedOs_Read',
			'data': {
				'req': {
					'nAdr': address,
					'param': {},
				},
			},
		};
		const options = new WebSocketOptions(request, timeout, message, callback);
		return store.dispatch('sendRequest', options);
	}

	/**
	 * Sends OS Reset request
	 * @param address Device address
	 * @param timeout Request timeout in milliseconds
	 * @param message Request timeout message
	 * @param callback Request timeout callback
	 * @return Request message ID
	 */
	reset(address: number, timeout: number, message: string|null = null, callback: CallableFunction = () => {return;}): Promise<string> {
		const request = {
			'mType': 'iqrfEmbedOs_Reset',
			'data': {
				'req': {
					'nAdr': address,
					'param': {},
				},
				'returnVerbose': true
			},
		};
		const options = new WebSocketOptions(request, timeout, message, callback);
		return store.dispatch('sendRequest', options);
	}

}

export default new OsService();
