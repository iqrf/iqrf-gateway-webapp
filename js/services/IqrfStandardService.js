import store from '../store';

/**
 * IQRF Standard service
 */
class IqrfStandardService {
	/**
	 * Performs Binary Output enumeration on device specified by address.
	 * @param address Node address
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
	 * Retrieves states of binary outputs.
	 * @param address Node address
	 */
	binaryOutputGetOutputs(address) {
		this.binaryOutputSetOutputs(address);
	}

	/**
	 * Sets binary outputs to a specified state.
	 * If no output settigs are specified, only previous states of binary outputs are retrieved.
	 * @param address Node address
	 * @param outputs New output setting
	 */
	binaryOutputSetOutputs(address, outputs = null) {
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

	/**
	 * Sends DALI commands to device specified by address.
	 * @param address Node address
	 * @param commands Array of DALI commands
	 */
	daliSend(address, commands) {
		store.dispatch('sendRequest', {
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

	/**
	 * Performs Light enumeration on device specified by address.
	 * @param address Node address
	 */
	lightEnumerate(address) {
		store.dispatch('sendRequest', {
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
	 * Retrieves current power of a light specified by index.
	 * @param address Node address
	 * @param light Light index
	 */
	lightGetPower(address, light) {
		this.lightSetPower(address, [{'index': light, 'power': 127}]);
	}

	/**
	 * Sets power of lights at a device specified by address.
	 * @param address Node address
	 * @param lights Object containing light settings
	 */
	lightSetPower(address, lights) {
		let json = {
			'mType': 'iqrfLight_SetPower',
			'data': {
				'req': {
					'nAdr': address,
					'param': {
						'lights': [],
					},
				},
				'returnVerbose': true,
			},
		};
		lights.forEach(item => {
			json.data.req.param.lights.push(item);
		});
		store.dispatch('sendRequest', json);
	}

	/**
	 * Increments power of light at a device specified by address.
	 * @param address Node address
	 * @param lights Object containing light settings
	 */
	lightIncrementPower(address, lights) {
		store.dispatch('sendRequest', {
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
	 * Decrements power of light at a device specified by address.
	 * @param address Node address
	 * @param lights Object containing light settings
	 */
	lightDecrementPower(address, lights) {
		store.dispatch('sendRequest', {
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
	 * Performs Sensor enumeration on device specified by address.
	 * @param address Node address
	 */
	sensorEnumerate(address) {
		store.dispatch('sendRequest', {
			'mType': 'iqrfSensor_Enumerate',
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
	 * Reads information from all sensors implemented by a device.
	 * @param address Node address
	 */
	sensorReadAll(address) {
		store.dispatch('sendRequest', {
			'mType': 'iqrfSensor_ReadSensorsWithTypes',
			'data': {
				'req': {
					'nAdr': address,
					'param': {
						'sensorIndexes': -1,
					},
				},
				'returnVerbose': true,
			},
		});
	}
}

export default new IqrfStandardService();
