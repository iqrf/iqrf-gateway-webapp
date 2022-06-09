/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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
import store from '@/store';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

export enum DaemonModeEnum {
	getMode = '',
	forwarding = 'forwarding',
	operational = 'operational',
	service = 'service',
	unknown = 'unknown',
}

/**
 * IQRF Gateway Daemon operational mode service
 */
class DaemonModeService {

	/**
	 * Retrieve the current operational mode
	 * @return Message ID
	 */
	get(timeout: number, message: string|null = null, callback: CallableFunction = () => {return;}): Promise<string> {
		return this.set(DaemonModeEnum.getMode, timeout, message, callback);
	}

	/**
	 * Sets a new mode operational mode
	 * @param newMode New operational mode
	 * @param timeout Timeout in milliseconds
	 * @param message Timeout message
	 * @param callback Timeout callback
	 * @return Message ID
	 */
	set(newMode: DaemonModeEnum, timeout: number, message: string|null = null, callback: CallableFunction = () => {return;}): Promise<string> {
		const request = {
			'mType': 'mngDaemon_Mode',
			'data': {
				'req': {
					'operMode': newMode,
				},
				'returnVerbose': true,
			},
		};
		const options = new DaemonMessageOptions(request, timeout, message, callback);
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Parses Daemon mode response
	 * @param response Response from IQRF Gateway Daemon
	 * @return Daemon mode
	 */
	parse(response: any): DaemonModeEnum {
		if (response.mType !== 'mngDaemon_Mode') {
			return DaemonModeEnum.unknown;
		}
		try {
			const mode = response.data.rsp.operMode;
			return mode as DaemonModeEnum;
		} catch (e) {
			return DaemonModeEnum.unknown;
		}
	}
}

export default new DaemonModeService();
