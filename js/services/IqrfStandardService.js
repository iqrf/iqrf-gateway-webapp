import store from '../store';

/**
 * IQRF Standard service
 */
class IqrfStandardService {
	/**
	 * Performs binary output enumeration on a device
	 * @param address Address of node implementing binary output standard
	 */
	binaryOutputEnumerate(address) {
		store.dispatch('sendRequest', {
			'mType': 'iqrfBinaryoutput_Enumerate',
			'data': {
				'req': {
					'nAdr': address,
					'param': {},
				},
				'returnVerbose': true,
			},
		});
	}

	/**
	 * Sets binary output to a specified state
	 * @param address Address of node implementing binary output standard
	 * @param outputs New output setting
	 */
	binaryOutputSetOutputs(address, outputs) {
		let json = {
			'mType': 'iqrfBinaryoutput_SetOutput',
			'data': {
				'req': {
					'nAdr': address,
					'param': {
						'binOuts': [],
					},
				},
				'returnVerbose': true,
			},
		};
		if (outputs) {
			json.data.req.param.binOuts.push(outputs);
		}
		store.dispatch('sendRequest', json);
	}
}

export default new IqrfStandardService();