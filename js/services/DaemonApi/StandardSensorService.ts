import store from '../../store';

/**
 * IQRF Standard Sensor service
 */
class StandardSensorService {

	/**
	 * Performs Sensor enumeration on device specified by address.
	 * @param address Node address
	 */
	enumerate(address: number): Promise<any> {
		return store.dispatch('sendRequest', {
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
	readAll(address: number): Promise<any> {
		return store.dispatch('sendRequest', {
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

export default new StandardSensorService();
