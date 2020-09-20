import store from '../../store';

/**
 * IQRF Standard DALI service
 */
class StandardDaliService {

	/**
	 * Sends DALI commands to device specified by address.
	 * @param address Node address
	 * @param commands Array of DALI commands
	 */
	send(address: number, commands: number[]): Promise<any> {
		return store.dispatch('sendRequest', {
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
		});
	}

}

export default new StandardDaliService();
