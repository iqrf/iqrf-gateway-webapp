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

import store from '@/store';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';
import {IOtaUploadParams} from '@/interfaces/DaemonApi/Iqmesh/OtaUpload';

/**
 * IQRF Network service
 */
class IqrfNetService {
	/**
	 * Performs AutoNetwork
	 * @param params Object containing AutoNetwork parameters
	 * @param {DaemonMessageOptions} options Daemon request options
	 * @return {Promise<string>} Message ID
	 */
	autoNetwork(params, options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'iqmeshNetwork_AutoNetwork',
			'data': {
				'req': params,
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Performs IQMESH Backup
	 * @param {number} address Device address
	 * @param {boolean} network Backup entire network
	 * @param {DaemonMessageOptions} options Daemon request options
	 * @return {Promise<string>} Message ID
	 */
	backup(address: number, network: boolean, options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'iqmeshNetwork_Backup',
			'data': {
				'req': {
					'deviceAddr': address,
					'wholeNetwork': network,
				},
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Bonds a node locally
	 * @param {number} address A requested address for the bonded node. If this parameter equals to 0, then the first free address is assigned to the node.
	 * @param {number} testRetries Number of FRC test requests to send
	 * @param {DaemonMessageOptions} options Daemon request options
	 * @return {Promise<string>} Message ID
	 */
	bondLocal(address: number, testRetries: number, options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'iqmeshNetwork_BondNodeLocal',
			'data': {
				'repeat': 1,
				'req': {
					'deviceAddr': address,
					'bondingTestRetries': testRetries,
				},
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Bonds NFC reader to address 240
	 * @param {DaemonMessageOptions} options Daemon request options
	 * @returns {Promise<string>} Message ID
	 */
	bondNfc(options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'iqrfRaw',
			'data': {
				'timeout': 11000,
				'req': {
					'rData': '00.00.00.04.ff.ff.f0.00',
				},
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Bonds a node via IQRF Smart Connect
	 * @param {number} address Address to bond the device to.  If this parameter equals to 0, then the first free address is assigned to the node.
	 * @param {string} scCode Device Smart Connect code
	 * @param {number} testRetries Maximum number of FRCs used to test whether the Node was successfully bonded
	 * @param {DaemonMessageOptions} options Daemon request options
	 * @return {Promise<string>} Message ID
	 */
	bondSmartConnect(address: number, scCode: string, testRetries: number, options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'iqmeshNetwork_SmartConnect',
			'data': {
				'repeat': 1,
				'req': {
					'deviceAddr': address,
					'smartConnectCode': scCode,
					'bondingTestRetries': testRetries,
				},
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Clears all bonds
	 * @param {boolean} coordinatorOnly Removes bonds only in the coordinator memory
	 * @param {DaemonMessageOptions} options Daemon request options
	 * @return {Promise<string>} Message ID
	 */
	clearAllBonds(coordinatorOnly: boolean, options: DaemonMessageOptions): Promise<string> {
		if (coordinatorOnly) {
			options.request = {
				'mType': 'iqmeshNetwork_RemoveBondOnlyInC',
				'data': {
					'req': {
						'deviceAddr': [],
						'clearAllBonds': true,
					},
					'returnVerbose': true,
				},
			};
			return store.dispatch('daemonClient/sendRequest', options);
		} else {
			return this.removeBond(255, false, options);
		}
	}

	/**
	 * Performs Coordinator discovery
	 * @param {number} txPower TX Power
	 * @param {number} maxAddr Maximum node address
	 * @param {DaemonMessageOptions} options Daemon request options
	 * @return {Promise<string>} Message ID
	 */
	discovery(txPower: number, maxAddr: number, options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'iqrfEmbedCoordinator_Discovery',
			'data': {
				'req': {
					'nAdr': 0,
					'param': {
						'txPower': txPower,
						'maxAddr': maxAddr,
					},
				},
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Performs device enumeration
	 * @param {number} address Device address
	 * @param {number} timeout Timeout in milliseconds
	 * @param {string|null} message Timeout message
	 * @param {CallableFunction} callback Timeout callback
	 * @return {Promise<string>} Message ID
	 */
	enumerateDevice(address: number, timeout: number, message: string|null = null, callback: CallableFunction = () => {return;}): Promise<string> {
		const request = {
			'mType': 'iqmeshNetwork_EnumerateDevice',
			'data': {
				'repeat': 1,
				'req': {
					'deviceAddr': address,
					'morePeripheralsInfo': true,
				},
				'returnVerbose': true,
			},
		};
		const options = new DaemonMessageOptions(request, timeout, message, callback);
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Retrieves list of bonded devices
	 * @param {DaemonMessageOptions} options Daemon request options
	 * @return {Promise<string>} Message ID
	 */
	getBonded(options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'iqrfEmbedCoordinator_BondedDevices',
			'data': {
				'req': {
					'nAdr': 0,
					'param': {},
				},
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Retrieves list of discovered devices
	 * @param {DaemonMessageOptions} options Daemon request options
	 * @return {Promise<string>} Message ID
	 */
	getDiscovered(options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'iqrfEmbedCoordinator_DiscoveredDevices',
			'data': {
				'req': {
					'nAdr': 0,
					'param': {},
				},
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Sends request to execute OTA upload action
	 * @param {IOtaUploadParams} params OTA upload parameters
	 * @param {DaemonMessageOptions} options Daemon request options
	 * @return {Promise<string>} Message ID
	 */
	otaUpload(params: IOtaUploadParams, options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'iqmeshNetwork_OtaUpload',
			'data': {
				'repeat': 1,
				'req': {
					'deviceAddr': params.address,
					'hwpId': params.hwpid,
					'fileName': params.file,
					'startMemAddr': params.startMemAddr,
					'loadingAction': params.loadingAction,
					'uploadEepromData': params.uploadEeprom,
					'uploadEeepromData': params.uploadEeeprom,
				},
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Perform FRC Ping
	 * @param {DaemonMessageOptions} options Daemon request options
	 * @return {Promise<string>} Message ID
	 */
	ping(options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'iqrfEmbedFrc_Send',
			'data': {
				'req': {
					'nAdr': 0,
					'param': {
						'frcCommand': 0,
						'userData': [0, 0],
					},
				},
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Sends FRC Ping to selected nodes
	 * @param nodes Array of selected nodes
	 * @param {DaemonMessageOptions} options Daemon request options
	 * @return {Promise<string>} Message ID
	 */
	pingSelective(nodes: Array<number>, options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'iqrfEmbedFrc_SendSelective',
			'data': {
				'req': {
					'nAdr': 0,
					'param': {
						'frcCommand': 0,
						'selectedNodes': nodes,
						'userData': [0, 0],
					},
				},
				'returnVerbose': true,
			}
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Removes a bond
	 * @param addr Address of a node bond to be removed
	 * @param coordinatorOnly Removes a bond only in the coordinator memory
	 * @param {DaemonMessageOptions} options Daemon request options
	 * @return {Promise<string>} Message ID
	 */
	removeBond(addr: number, coordinatorOnly: boolean, options: DaemonMessageOptions): Promise<string> {
		if (coordinatorOnly) {
			options.request = {
				'mType': 'iqmeshNetwork_RemoveBondOnlyInC',
				'data': {
					'repeat': 1,
					'req': {
						'deviceAddr': [addr],
					},
					'returnVerbose': true,
				},
			};
		} else {
			options.request = {
				'mType': 'iqmeshNetwork_RemoveBond',
				'data': {
					'repeat': 1,
					'req': {
						'deviceAddr': addr,
					},
					'returnVerbose': true,
				},
			};
		}
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Performs IQMESH restart, nodes to restart can be filtered by HWPID
	 * @param {number} hwpid HWPID filter
	 * @param {DaemonMessageOptions} options WebSocket request options
	 */
	restart(hwpid: number, options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'iqmeshNetwork_Restart',
			'data': {
				'req': {
					'hwpId': hwpid,
				},
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Performs IQMESH Restore
	 * @param address Device address
	 * @param restart Restart coordinator on restore
	 * @param data Backup data
	 * @param {DaemonMessageOptions} options Daemon request options
	 * @return {Promise<string>} Message ID
	 */
	restore(address: number, restart: boolean, data: string, options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'iqmeshNetwork_Restore',
			'data': {
				'req': {
					'deviceAddr': address,
					'data': data,
					'restartCoordinator': restart
				},
				'returnVerbose': true,
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Sends JSON API request
	 * @param {DaemonMessageOptions} options Daemon request options
	 * @return {Promise<string>} Message ID
	 */
	sendJson(options: DaemonMessageOptions): Promise<string> {
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Writes TR configuration
	 * @param address Device address to write the configuration to
	 * @param hwpid HWPID to filter devices by
	 * @param configuration New TR configuration
	 * @param timeout Timeout in milliseconds
	 * @param message Timeout message
	 * @param callback Timeout callback
	 * @return {Promise<string>} Message ID
	 */
	writeTrConfiguration(address: number, hwpid: number, configuration: any, timeout: number, message: string|null = null, callback: CallableFunction = () => {return;}): Promise<string> {
		configuration.deviceAddr = address;
		if (address === 255) {
			configuration.hwpId = hwpid;
		}
		const request = {
			'mType': 'iqmeshNetwork_WriteTrConf',
			'data': {
				'repeat': 1,
				'req': configuration,
				'returnVerbose': true,
			},
		};
		const options = new DaemonMessageOptions(request, timeout, message, callback);
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Sends a batch request to indicate coordinator LEDs for 5 seconds
	 * @param {DaemonMessageOptions} options WebSocket request options
	 * @return {Promise<string>} Message ID
	 */
	indicateCoordinator(options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'iqrfEmbedOs_Batch',
			'data': {
				'msgId': 'testEmbedOs',
				'req': {
					'nAdr': 0,
					'param': {
						'requests': [
							{
								'hwpid': 'ffff',
								'pnum': '06',
								'pcmd': '03'
							},
							{
								'hwpid': 'ffff',
								'pnum': '07',
								'pcmd': '03'
							}
						]
					}
				},
				'returnVerbose': true
			}
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}

	/**
	 * Sets FRC response time
	 * @param {number} responseTime Response time
	 * @param {DaemonMessageOptions} options Daemon message options
	 * @return {Promise<string>} Message ID
	 */
	setFrcResponseTime(responseTime: number, options: DaemonMessageOptions): Promise<string> {
		options.request = {
			'mType': 'iqrfEmbedFrc_SetParams',
			'data': {
				'req': {
					'nAdr': 0,
					'param': {
						'frcResponseTime': responseTime,
					},
				},
			},
		};
		return store.dispatch('daemonClient/sendRequest', options);
	}
}

export default new IqrfNetService();
 