import store from '../../store';
import { WebSocketOptions } from '../../store/modules/webSocketClient.module';

/**
 * IQRF Standard DALI service
 */
class StandardDaliService {

	/**
	 * Sends DALI commands to device specified by address.
	 * @param address Node address
	 * @param commands Array of DALI commands
	 * @param options WebSocket request option
	 * @return Message ID
	 */
	send(address: number, commands: number[], options: WebSocketOptions): Promise<string> {
		options.request = {
			'mType': 'iqrfDali_SendCommands',
			'data': {
				'req': {
					'nAdr': address,
					'param': {
						'commands': commands,
					},
				},
				'returnVerbose': true,
			},
		};
		return store.dispatch('sendRequest', options);
	}

}

export default new StandardDaliService();
