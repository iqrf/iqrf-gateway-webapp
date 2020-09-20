import store from '../../store';

/**
 * IQRF Standard light service
 */
class StandardLightService {

	/**
	 * Performs Light enumeration on device specified by address.
	 * @param address Node address
	 */
	enumerate(address: number): Promise<any> {
		return store.dispatch('sendRequest', {
			'mType': 'iqrfLight_Enumerate',
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
	 * Decrements power of light at a device specified by address.
	 * @param address Node address
	 * @param lights Object containing light settings
	 */
	decrementPower(address: number, lights: any[]): Promise<any> {
		return store.dispatch('sendRequest', {
			'mType': 'iqrfLight_DecrementPower',
			'data': {
				'req': {
					'nAdr': address,
					'param': {
						'lights': lights,
					},
				},
				'returnVerbose': true,
			},
		});
	}

	/**
	 * Increments power of light at a device specified by address.
	 * @param address Node address
	 * @param lights Object containing light settings
	 */
	incrementPower(address: number, lights: any[]): Promise<any> {
		return store.dispatch('sendRequest', {
			'mType': 'iqrfLight_IncrementPower',
			'data': {
				'req': {
					'nAdr': address,
					'param': {
						'lights': lights,
					},
				},
				'returnVerbose': true,
			},
		});
	}

	/**
	 * Retrieves current power of a light specified by index.
	 * @param address Node address
	 * @param light Light index
	 */
	getPower(address: number, light: any): Promise<any> {
		return this.setPower(address, [{'index': light, 'power': 127}]);
	}

	/**
	 * Sets power of lights at a device specified by address.
	 * @param address Node address
	 * @param lights Object containing light settings
	 */
	setPower(address: number, lights: any[]): Promise<any> {
		return store.dispatch('sendRequest', {
			'mType': 'iqrfLight_SetPower',
			'data': {
				'req': {
					'nAdr': address,
					'param': {
						'lights': lights,
					},
				},
				'returnVerbose': true,
			},
		});
	}

}

export default new StandardLightService();
