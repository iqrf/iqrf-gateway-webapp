/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
import {EnumerateCommand} from '@/enums/IqrfNet/info';
import store from '@/store';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';
import {TranslateResult} from 'vue-i18n';

/**
 * IQRF Info service
 */
class InfoService {

	binouts(timeout: number|null = null, message: TranslateResult|null = null, callback: CallableFunction = () => {return;}): Promise<string> {
		const request = {
			'mType': 'infoDaemon_GetBinaryOutputs',
			'data': {
				'msgId': 'test',
				'req': {},
				'returnVerbose': true,
			},
		};
		const options = new DaemonMessageOptions(request, timeout, message, callback);
		return store.dispatch('daemonClient/sendRequest', options);
	}

	dalis(timeout: number|null = null, message: TranslateResult|null = null, callback: CallableFunction = () => {return;}): Promise<string> {
		const request = {
			'mType': 'infoDaemon_GetDalis',
			'data': {
				'req': {},
				'returnVerbose': true,
			},
		};
		const options = new DaemonMessageOptions(request, timeout, message, callback);
		return store.dispatch('daemonClient/sendRequest', options);
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
	enumerate(command: EnumerateCommand, period: number|null = null, timeout: number|null = null, message: TranslateResult|null = null, callback: CallableFunction = () => {return;}): Promise<string> {
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
		const options = new DaemonMessageOptions(request, timeout, message, callback);
		return store.dispatch('daemonClient/sendRequest', options);
	}

	lights(timeout: number|null = null, message: TranslateResult|null = null, callback: CallableFunction = () => {return;}): Promise<string> {
		const request = {
			'mType': 'infoDaemon_GetLights',
			'data': {
				'msgId': 'test',
				'req': {},
				'returnVerbose': true,
			}
		};
		const options = new DaemonMessageOptions(request, timeout, message, callback);
		return store.dispatch('daemonClient/sendRequest', options);
	}

	nodes(timeout: number|null = null, message: TranslateResult|null = null, callback: CallableFunction = () => {return;}): Promise<string> {
		const request = {
			'mType': 'infoDaemon_GetNodes',
			'data': {
				'msgId': 'testGetNodes',
				'req': {},
				'returnVerbose': true,
			},
		};
		const options = new DaemonMessageOptions(request, timeout, message, callback);
		return store.dispatch('daemonClient/sendRequest', options);
	}

	sensors(timeout: number|null = null, message: TranslateResult|null = null, callback: CallableFunction = () => {return;}): Promise<string> {
		const request = {
			'mType': 'infoDaemon_GetSensors',
			'data': {
				'msgId': 'test',
				'req': {},
				'returnVerbose': true,
			},
		};
		const options = new DaemonMessageOptions(request, timeout, message, callback);
		return store.dispatch('daemonClient/sendRequest', options);
	}

	reset(timeout: number|null = null, message: TranslateResult|null = null, callback: CallableFunction = () => {return;}): Promise<string> {
		const request = {
			'mType': 'infoDaemon_Reset',
			'data': {
				'msgId': 'test',
				'req': {},
				'returnVerbose': true,
			},
		};
		const options = new DaemonMessageOptions(request, timeout, message, callback);
		return store.dispatch('daemonClient/sendRequest', options);
	}
}

export default new InfoService();
