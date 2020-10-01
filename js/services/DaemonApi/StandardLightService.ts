import store from '../../store';

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
	decrementPower(address: number, lights: StandardLight[]): Promise<any> {
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
	incrementPower(address: number, lights: StandardLight[]): Promise<any> {
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
	getPower(address: number, light: number): Promise<any> {
		return this.setPower(address, [new StandardLight(light, 127)]);
	}

	/**
	 * Sets power of lights at a device specified by address.
	 * @param address Node address
	 * @param lights Object containing light settings
	 */
	setPower(address: number, lights: StandardLight[]): Promise<any> {
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
