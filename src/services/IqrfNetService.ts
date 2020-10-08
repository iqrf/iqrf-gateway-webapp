import store from '../store';
import {WebSocketOptions} from '../store/modules/webSocketClient.module';

/**
 * IQRF Network service
 */
class IqrfNetService {
	/**
	 * Performs AutoNetwork
	 * @param autoNetwork Object containing AutoNetwork parameters
	 * @param options WebSocket request options
	 */
	autoNetwork(autoNetwork: any, options: WebSocketOptions) {
		const json = {
			'mType': 'iqmeshNetwork_AutoNetwork',
			'data': {
				'req': {
					'discoveryTxPower': autoNetwork.discoveryTxPower,
					'discoveryBeforeStart': autoNetwork.discoveryBeforeStart,
					'skipDiscoveryEachWave': autoNetwork.skipDiscoveryEachWave,
					'actionRetries': autoNetwork.actionRetries,
				},
				'returnVerbose': true,
			},
		};
		if (autoNetwork.stopConditions) {
			Object.assign(json.data.req, {'stopConditions': autoNetwork.stopConditions});
		}
		if (autoNetwork.overlappingNetworks) {
			Object.assign(json.data.req, {'overlappingNetworks': autoNetwork.overlappingNetworks});
		}
		if (autoNetwork.hwpidFiltering) {
			Object.assign(json.data.req, {'hwpidFiltering': autoNetwork.hwpidFiltering});
		}
		options.request = json;
		return store.dispatch('sendRequest', options);
	}

	/**
	 * Bonds a node locally
	 * @param address A requested address for the bonded node. If this parameter equals to 0, then the first free address is assigned to the node.
	 * @param options WebSocket request options
	 */
	bondLocal(address: number, options: WebSocketOptions) {
		options.request = {
			'mType': 'iqmeshNetwork_BondNodeLocal',
			'data': {
				'repeat': 2,
				'req': {
					'deviceAddr': address,
				},
				'returnVerbose': true,
			},
		};
		return store.dispatch('sendRequest', options);
	}

	/**
	 * Bonds a node via IQRF Smart Connect
	 * @param address Address to bond the device to.  If this parameter equals to 0, then the first free address is assigned to the node.
	 * @param scCode Device Smart Connect code
	 * @param testRetries Maximum number of FRCs used to test whether the Node was successfully bonded
	 * @param options WebSocket request options
	 */
	bondSmartConnect(address: number, scCode: string, testRetries: number, options: WebSocketOptions): Promise<any> {
		options.request = {
			'mType': 'iqmeshNetwork_SmartConnect',
			'data': {
				'repeat': 2,
				'req': {
					'deviceAddr': address,
					'smartConnectCode': scCode,
					'bondingTestRetries': testRetries,
				},
				'returnVerbose': true,
			},
		};
		return store.dispatch('sendRequest', options);
	}

	/**
	 * Clears all bonds
	 * @param coordinatorOnly Removes bonds only in the coordinator memory
	 * @param options WebSocket request options
	 */
	clearAllBonds(coordinatorOnly: boolean, options: WebSocketOptions): Promise<any> {
		if (coordinatorOnly) {
			options.request = {
				'mType': 'iqrfEmbedCoordinator_ClearAllBonds',
				'data': {
					'req': {
						'nAdr': 0,
						'param': {},
					},
					'returnVerbose': true,
				},
			};
			return store.dispatch('sendRequest', options);
		} else {
			return this.removeBond(255, false, options);
		}
	}

	/**
	 * Performs Coordinator discovery
	 * @param txPower TX Power
	 * @param maxAddr Maximum node address
	 */
	discovery(txPower: number, maxAddr: number, options: WebSocketOptions): Promise<any> {
		options.request = {
			'mType': 'iqrfEmbedCoordinator_Discovery',
			'data': {
				'repeat': 2,
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
		return store.dispatch('sendRequest', options);
	}

	/**
	 * Performs device enumeration
	 * @param address Device address
	 */
	enumerateDevice(address: number, timeout: number, message: string|null = null, callback: CallableFunction = () => {return;}): Promise<any> {
		const request = {
			'mType': 'iqmeshNetwork_EnumerateDevice',
			'data': {
				'repeat': 2,
				'req': {
					'deviceAddr': address,
					'morePeripheralsInfo': true,
				},
				'returnVerbose': true,
			},
		};
		const options = new WebSocketOptions(request, timeout, message, callback);
		return store.dispatch('sendRequest', options);
	}

	/**
	 * Retrieves list of bonded devices
	 * @param options WebSocket request options
	 */
	getBonded(options: WebSocketOptions): Promise<any> {
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
		return store.dispatch('sendRequest', options);
	}

	/**
	 * Retrieves list of discovered devices
	 * @param options WebSocket request options
	 */
	getDiscovered(options: WebSocketOptions): Promise<any> {
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
		return store.dispatch('sendRequest', options);
	}

	/**
	 * Perform FRC Ping
	 * @param options WebSocket request options
	 */
	ping(options: WebSocketOptions): Promise<any> {
		options.request = {
			'mType': 'iqrfEmbedFrc_Send',
			'data': {
				'req': {
					'nAdr': 0,
					'param': {
						'frcCommand': 0,
						'userData': [0, 0],
					},
					'returnVerbose': true,
				},
			},
		};
		return store.dispatch('sendRequest', options);
	}

	/**
	 * Removes a bond
	 * @param addr Address of a node bond to be removed
	 * @param coordinatorOnly Removes a bond only in the coordinator memory
	 * @param options WebSocket request options
	 */
	removeBond(addr: number, coordinatorOnly: boolean, options: WebSocketOptions): Promise<any> {
		if (coordinatorOnly) {
			options.request = {
				'mType': 'iqrfEmbedCoordinator_RemoveBond',
				'data': {
					'req': {
						'nAdr': 0,
						'param': {
							'bondAddr': addr,
						},
					},
					'returnVerbose': true,
				},
			};
		} else {
			options.request = {
				'mType': 'iqmeshNetwork_RemoveBond',
				'data': {
					'repeat': 2,
					'req': {
						'deviceAddr': addr,
					},
					'returnVerbose': true,
				},
			};
		}
		return store.dispatch('sendRequest', options);
	}

	/**
	 * Sends JSON API request
	 * @param json JSON API request string
	 */
	sendJson(json: any, timeout: number, message: string|null = null, callback: CallableFunction = () => {return;}): Promise<any> {
		const options = new WebSocketOptions(json, timeout, message, callback);
		return store.dispatch('sendRequest', options);
	}

	/**
	 * Writes TR configuration
	 * @param address Device address to write the configuration to
	 * @param configuration New TR configuration
	 */
	writeTrConfiguration(address: number, configuration: any, timeout: number, message: string|null = null, callback: CallableFunction = () => {return;}): Promise<any> {
		delete configuration.rfBand;
		configuration.deviceAddr = address;
		const request = {
			'mType': 'iqmeshNetwork_WriteTrConf',
			'data': {
				'repeat': 2,
				'req': configuration,
				'returnVerbose': true,
			},
		};
		const options = new WebSocketOptions(request, timeout, message, callback);
		return store.dispatch('sendRequest', options);
	}
}

export default new IqrfNetService();
