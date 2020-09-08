import store from '../store';

/**
 * IQRF TR enumeration service
 */
class IqmeshNetworkService {
	/**
	 * Performs device enumeration
	 * @param address Device address
	 */
	enumerateDevice(address) {
		store.dispatch('sendRequest', {
			'mType': 'iqmeshNetwork_EnumerateDevice',
			'data': {
				'repeat': 2,
				'req': {
					'deviceAddr': address,
					'morePeripheralsInfo': true,
				},
				'returnVerbose': true,
			},
		});
	}

	/**
	 * Writes TR configuration
	 * @param address Device address to write the configuration to
	 * @param configuration New TR configuration
	 */
	writeTrConfiguration(address, configuration) {
		delete configuration.rfBand;
		configuration.deviceAddr = address;
		store.dispatch('sendRequest', {
			'mType': 'iqmeshNetwork_WriteTrConf',
			'data': {
				'repeat': 2,
				'req': configuration,
				'returnVerbose': true,
			},
		});
	}
}

export default new IqmeshNetworkService();
