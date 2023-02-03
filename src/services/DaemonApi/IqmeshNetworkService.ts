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

import store from '../../store';
import DaemonMessageOptions from '../../ws/DaemonMessageOptions';

import {DpaParamAction, DpaValueType} from '@/enums/IqrfNet/DpaParams';
import {FrcCommands} from '@/enums/IqrfNet/Maintenance';

import {IRfSignalTestParams} from '@/interfaces/DaemonApi/Iqmesh/Maintenance';

/**
 * IQMESH Network service
 */
class IqmeshNetworkService {
	/**
	 * IQMESH Network DPA Params DPA Hops request
	 * @param {DpaParamAction} action DPA param action
	 * @param {number|null} requestHops Request hops
	 * @param {number|null} responseHops Response hops
	 * @param {DaemonMessageOptions} options Daemon message options
	 * @returns {Promise<string>} Message ID
	 */
	dpaHops(action: DpaParamAction, requestHops: number | null = null, responseHops: number | null = null, options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'iqmeshNetwork_DpaHops',
			'data': {
				'req': {
					'action': action,
				},
				'repeat': 1,
				'returnVerbose': true,
			},
		};
		if (action === DpaParamAction.SET) {
			if (requestHops !== null) {
				Object.assign(options.request.data.req, { requestHops: requestHops });
			}
			if (responseHops !== null) {
				Object.assign(options.request.data.req, { responseHops: responseHops });
			}
		}
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * IQMESH Network DPA Params DPA Value request
	 * @param {DpaParamAction} action DPA param action
	 * @param {DpaValueType} type DPA value type
	 * @param {DaemonMessageOptions} options Daemon message options
	 * @returns {Promise<string>} Message ID
	 */
	dpaValue(action: DpaParamAction, type: DpaValueType | null = null, options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'iqmeshNetwork_DpaValue',
			'data': {
				'req': {
					'action': action,
				},
				'repeat': 1,
				'returnVerbose': true,
			},
		};
		if (action === DpaParamAction.SET && type !== null) {
			Object.assign(options.request.data.req, { type: type });
		}
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * IQMESH Network DPA Params FRC Params request
	 * @param {DpaParamAction} action DPA param action
	 * @param {number|null} responseTime FRC response time
	 * @param {boolean|null} offlineFrc Offline FRC
	 * @param {DaemonMessageOptions} options Daemon message options
	 * @returns {Promise<string>} Message ID
	 */
	frcParams(action: DpaParamAction, responseTime: number | null = null, offlineFrc: boolean | null = null, options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'iqmeshNetwork_FrcParams',
			'data': {
				'req': {
					'action': action,
				},
				'repeat': 1,
				'returnVerbose': true,
			},
		};
		if (action === DpaParamAction.SET) {
			if (responseTime !== null) {
				Object.assign(options.request.data.req, { responseTime: responseTime });
			}
			if (offlineFrc !== null) {
				Object.assign(options.request.data.req, { offlineFrc: offlineFrc });
			}
		}
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * IQMESH Network Maintenance FRC Response Time request
	 * @param {FrcCommands} command FRC command to test
	 * @param {DaemonMessageOptions} options Daemon message options
	 * @returns {Promise<string>} Message ID
	 */
	maintenanceFrcResponseTime(command: FrcCommands, options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'iqmeshNetwork_MaintenanceFrcResponseTime',
			'data': {
				'req': {
					'command': command,
				},
				'repeat': 1,
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * IQMESH Network Maintenance Test RF Signal request
	 * @param {IRfSignalTestParams} params Request parameters
	 * @param {DaemonMessageOptions} options Daemon message options
	 * @returns {Promise<string>} Message ID
	 */
	maintenanceTestRfSignal(params: IRfSignalTestParams, options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'iqmeshNetwork_MaintenanceTestRF',
			'data': {
				'repeat': 1,
				'returnVerbose': true,
			},
		};
		Object.assign(options.request.data, { req: params });
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * IQMESH Network Maintenance Inconsistent MIDs in Coordinator request
	 * @param {DaemonMessageOptions} options Daemon message options
	 * @returns {Promise<string>} Message ID
	 */
	maintenanceInconsistentMidsInCoordinator(options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'iqmeshNetwork_MaintenanceInconsistentMIDsInCoord',
			'data': {
				'repeat': 1,
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * IQMESH Network Duplicated Addresses request
	 * @param {DaemonMessageOptions} options Daemon message options
	 * @returns {Promise<string>} Message ID
	 */
	maintenanceDuplicatedAddress(options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'iqmeshNetwork_MaintenanceDuplicatedAddresses',
			'data': {
				'repeat': 1,
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * IQMESH Network Maintenance Useless Prebonded Nodes request
	 * @param {DaemonMessageOptions} options Daemon message options
	 * @returns {Promise<string>} Message ID
	 */
	maintenanceUselessPrebondedNodes(options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'iqmeshNetwork_MaintenanceUselessPrebondedNodes',
			'data': {
				'repeat': 1,
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * IQMESH Network Ping request
	 * @param {DaemonMessageOptions} options Daemon message options
	 * @param {number|null} hwpid Target HWPID
	 * @returns {Promise<string>} Message ID
	 */
	ping(options: DaemonMessageOptions, hwpid: number | null = null): Promise<string> {
		const json = {
			'mType': 'iqmeshNetwork_Ping',
			'data': {
				'req': {},
				'repeat': 1,
				'returnVerbose': true,
			},
		};
		if (hwpid !== null) {
			Object.assign(json.data.req, { hwpId: hwpid });
		}
		options.request = json;
		return store.dispatch('daemonClient/sendRequest', options);
	}
}

export default new IqmeshNetworkService();
 