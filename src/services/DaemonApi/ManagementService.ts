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

import {DaemonModeEnum} from '@/enums/Gateway/DaemonMode';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';
import store from '@/store';

/**
 * Daemon API management service
 */
class ManagementService {
	/**
	 * Schedules daemon exit
	 * @param {number} timeToExit Time to exit in milliseconds
	 * @param {DaemonMessageOptions} options Daemon API request options
	 * @returns {Promise<string>} A promise containing message ID when fulfilled
	 */
	exit(timeToExit: number, options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'mngDaemon_Exit',
			'data': {
				'req': {
					'timeToExit': timeToExit,
				},
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Get current Daemon mode
	 * @param {DaemonMessageOptions} options Daemon API request options
	 * @returns {Promise<string>} A promise containing message ID when fulfilled
	 */
	getMode(options: DaemonMessageOptions): Promise<string> {
		return this.setMode(DaemonModeEnum.getMode, options);
	}

	/**
	 * Set Daemon mode
	 * @param {DaemonModeEnum} mode Daemon mode
	 * @param {DaemonMessageOptions} options Daemon API request options
	 * @returns {Promise<string>} A promise containing message ID when fulfilled
	 */
	setMode(mode: DaemonModeEnum, options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'mngDaemon_Mode',
			'data': {
				'req': {
					'operMode': mode,
				},
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Retrieves IQRF Gateway Daemon version
	 * @param {DaemonMessageOptions} options Daemon API request options
	 * @returns {Promise<string>} A promise containing message ID when fulfilled
	 */
	getVersion(options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'mngDaemon_Version',
			'data': {
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Reinitializes C device
	 * @param {DaemonMessageOptions} options Daemon API request options
	 * @returns {Promise<string>} A promise containing message ID when fulfilled
	 */
	reloadCoordinator(options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'mngDaemon_ReloadCoordinator',
			'data': {
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Updates iqrf repository cache
	 * @param {DaemonMessageOptions} options Daemon API request options
	 * @returns {Promise<string>} A promise containing message ID when fulfilled
	 */
	updateCache(options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'mngDaemon_UpdateCache',
			'data': {
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Parses Daemon mode response
	 * @param response Response from IQRF Gateway Daemon
	 * @return {DaemonModeEnum} Daemon mode
	 */
	parseModeResponse(response): DaemonModeEnum {
		if (response.mType !== 'mngDaemon_Mode') {
			return DaemonModeEnum.unknown;
		}
		try {
			const mode = response.data.rsp.operMode;
			return mode as DaemonModeEnum;
		} catch {
			return DaemonModeEnum.unknown;
		}
	}
}

export default new ManagementService();
