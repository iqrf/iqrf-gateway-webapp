import store from '../../store';
import { WebSocketOptions } from '../../store/modules/webSocketClient.module';

/**
 * IQRF Standard Sensor service
 */
class StandardSensorService {

	/**
	 * Performs Sensor enumeration on device specified by address.
	 * @param address Node address
	 * @param options WebSocket request options
	 * @return Message ID
	 */
	enumerate(address: number, options: WebSocketOptions): Promise<string> {
		options.request = {
			'mType': 'iqrfSensor_Enumerate',
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

	/**
	 * Reads information from all sensors implemented by a device.
	 * @param address Node address
	 * @param options WebSocket request option
	 * @return Message ID
	 */
	readAll(address: number, options: WebSocketOptions): Promise<string> {
		options.request = {
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
		};
		return store.dispatch('sendRequest', options);
	}

}

export default new StandardSensorService();
