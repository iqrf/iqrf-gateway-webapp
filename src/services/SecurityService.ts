import store from '../store';
import {SecurityFormat} from '../iqrfNet/securityFormat';
import { WebSocketOptions } from '../store/modules/webSocketClient.module';

/**
 * TR configuration security service
 */
class SecurityService {

	/**
	 * Sets IQMESH security
	 * @param nadr Network device address
	 * @param password 
	 * @param inputFormat 
	 * @param type 
	 */
	setSecurity(nadr: number, password: string, inputFormat: SecurityFormat, type: number, 
		timeout: number, message: string|null = null, callback: CallableFunction = () => {return;}): Promise<any> {
		const request = {
			'mType': 'iqrfEmbedOs_SetSecurity',
			'data': {
				'req': {
					'nAdr': nadr,
					'param': {
						'type': type,
						'data': this.convert(password, inputFormat),
					},
				},
				'returnVerbose': true,
			},
		};
		const options = new WebSocketOptions(request, timeout, message, callback);
		return store.dispatch('sendRequest', options);
	}

	/**
	 * Converts an access password or an user key to HEX format
	 * @param password access password or user key
	 * @param format input data format (ASCII or HEX)
	 */
	convert(password: string, format: SecurityFormat): Array<number> {
		let data = password;
		if (format === SecurityFormat.ASCII) {
			data = '';
			for (let i = 0; i < password.length; ++i) {
				data += password.charCodeAt(i).toString(16);
			}
		}
		data = data.padEnd(32, '0');
		const array: Array<number> = [];
		for (let i = 0; i < 16; ++i) {
			array.push(parseInt(data.substr(i*2, 2), 16));
		}
		return array;
	}
}

export default new SecurityService();
