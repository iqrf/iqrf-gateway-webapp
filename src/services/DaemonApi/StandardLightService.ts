import store from '../../store';
import { WebSocketOptions } from '../../store/modules/webSocketClient.module';

export class StandardLight {

	/**
	 * Index of the light
	 */
	public index: number;

	/**
	 * Power level of the light from range <0;100>
	 */
	public power: number;

	/**
	 * Constructor
	 * @param index Index of the light
	 * @param power Power level of the light from range <0;100>
	 */
	public constructor(index: number, power: number) {
		this.index = index;
		this.power = power;
	}

}

/**
 * IQRF Standard light service
 */
class StandardLightService {

	/**
	 * Performs Light enumeration on device specified by address.
	 * @param address Node address
	 * @param options WebSocket request option
	 */
	enumerate(address: number, options: WebSocketOptions): Promise<any> {
		options.request = {
			'mType': 'iqrfLight_Enumerate',
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
	 * Decrements power of light at a device specified by address.
	 * @param address Node address
	 * @param lights Object containing light settings
	 * @param options WebSocket request option
	 */
	decrementPower(address: number, lights: StandardLight[], options: WebSocketOptions): Promise<any> {
		options.request = {
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
		};
		return store.dispatch('sendRequest', options);
	}

	/**
	 * Increments power of light at a device specified by address.
	 * @param address Node address
	 * @param lights Object containing light settings
	 * @param options WebSocket request option
	 */
	incrementPower(address: number, lights: StandardLight[], options: WebSocketOptions): Promise<any> {
		options.request = {
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
		};
		return store.dispatch('sendRequest', options);
	}

	/**
	 * Retrieves current power of a light specified by index.
	 * @param address Node address
	 * @param light Light index
	 * @param options WebSocket request option
	 */
	getPower(address: number, light: number, options: WebSocketOptions): Promise<any> {
		return this.setPower(address, [new StandardLight(light, 127)], options);
	}

	/**
	 * Sets power of lights at a device specified by address.
	 * @param address Node address
	 * @param lights Object containing light settings
	 * @param options WebSocket request option
	 */
	setPower(address: number, lights: StandardLight[], options: WebSocketOptions): Promise<any> {
		options.request = {
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
		};
		return store.dispatch('sendRequest', options);
	}

}

export default new StandardLightService();
