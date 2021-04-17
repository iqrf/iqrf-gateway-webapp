import {EnumerateCommand} from '../../enums/IqrfNet/info';
import store from '../../store';
import {WebSocketOptions} from '../../store/modules/webSocketClient.module';

/**
 * IQRF Info service
 */
class InfoService {

	binouts(timeout: number|null = null, message: string|null = null, callback: CallableFunction = () => {return;}): Promise<string> {
		const request = {
			'mType': 'infoDaemon_GetBinaryOutputs',
			'data': {
				'msgId': 'test',
				'req': {},
				'returnVerbose': true,
			},
		};
		const options = new WebSocketOptions(request, timeout, message, callback);
		return store.dispatch('sendRequest', options);
	}

	dalis(timeout: number|null = null, message: string|null = null, callback: CallableFunction = () => {return;}): Promise<string> {
		const request = {
			'mType': 'infoDaemon_GetDalis',
			'data': {
				'req': {},
				'returnVerbose': true,
			},
		};
		const options = new WebSocketOptions(request, timeout, message, callback);
		return store.dispatch('sendRequest', options);
	}
	
	/**
	 * Sends network enumerate request
	 * @param command Enumeration command to execute
	 * @param period Enumeration period to set
	 * @param timeout Request timeout in milliseconds
	 * @param message Request timeout message
	 * @param callback Request timeout callback
	 * @returns Request message ID
	 */
	enumerate(command: EnumerateCommand, period: number|null = null, timeout: number|null = null, message: string|null = null, callback: CallableFunction = () => {return;}): Promise<string> {
		const request = {
			'mType': 'infoDaemon_Enumeration',
			'data': {
				'req': {
					'command': command,
				},
				'returnVerbose': true,
			},
		};
		if (command === EnumerateCommand.SETPERIOD) {
			Object.assign(request.data.req, {period: period});
		}
		const options = new WebSocketOptions(request, timeout, message, callback);
		return store.dispatch('sendRequest', options);
	}

	lights(timeout: number|null = null, message: string|null = null, callback: CallableFunction = () => {return;}): Promise<string> {
		const request = {
			'mType': 'infoDaemon_GetLights',
			'data': {
				'msgId': 'test',
				'req': {},
				'returnVerbose': true,
			}
		};
		const options = new WebSocketOptions(request, timeout, message, callback);
		return store.dispatch('sendRequest', options);
	}

	nodes(timeout: number|null = null, message: string|null = null, callback: CallableFunction = () => {return;}): Promise<string> {
		const request = {
			'mType': 'infoDaemon_GetNodes',
			'data': {
				'msgId': 'testGetNodes',
				'req': {},
				'returnVerbose': true,
			},
		};
		const options = new WebSocketOptions(request, timeout, message, callback);
		return store.dispatch('sendRequest', options);
	}

	sensors(timeout: number|null = null, message: string|null = null, callback: CallableFunction = () => {return;}): Promise<string> {
		const request = {
			'mType': 'infoDaemon_GetSensors',
			'data': {
				'msgId': 'test',
				'req': {},
				'returnVerbose': true,
			},
		};
		const options = new WebSocketOptions(request, timeout, message, callback);
		return store.dispatch('sendRequest', options);
	}

	reset(timeout: number|null = null, message: string|null = null, callback: CallableFunction = () => {return;}): Promise<string> {
		const request = {
			'mType': 'infoDaemon_Reset',
			'data': {
				'msgId': 'test',
				'req': {},
				'returnVerbose': true,
			},
		};
		const options = new WebSocketOptions(request, timeout, message, callback);
		return store.dispatch('sendRequest', options);
	}
}

export default new InfoService();
