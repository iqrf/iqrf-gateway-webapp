import store from '../../store';
import { WebSocketOptions } from '../../store/modules/webSocketClient.module';

/**
 * OS service
 */
class OsService {

	/**
	 * Sends OS Read request
	 * @param address Address
	 */
	sendRead(address: number, timeout: number, message: string|null = null, callback: CallableFunction = () => {return;}): Promise<any> {
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

}

export default new OsService();
