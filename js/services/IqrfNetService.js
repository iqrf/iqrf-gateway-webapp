import store from '../store';

/**
 * IQRF Network service
 */
class IqrfNetService {
	/**
	 * Performs AutoNetwork
	 * @param autoNetwork Object containing AutoNetwork parameters
	 */
	autoNetwork(autoNetwork) {
		store.dispatch('sendRequest', {
			'mType': 'iqmeshNetwork_AutoNetwork',
			'data': {
				'req': {
					'discoveryTxPower': autoNetwork.discoveryTxPower,
					'discoveryBeforeStart': autoNetwork.discoveryBeforeStart,
					'skipDiscoveryEachWave': autoNetwork.skipDiscoveryEachWave,
					'actionRetries': autoNetwork.actionRetries,
					'overlappingNetworks': {
						'networks': autoNetwork.overlappingNetworks.networks,
						'network': autoNetwork.overlappingNetworks.network,
					},
					'hwpidFiltering': autoNetwork.hwpidFiltering,
					'stopConditions': {
						'waves': autoNetwork.stopConditions.waves,
						'emptyWaves': autoNetwork.stopConditions.emptyWaves,
						'numberOfTotalNodes': autoNetwork.stopConditions.numberOfTotalNodes,
						'numberOfNewNodes': autoNetwork.stopConditions.numberOfNewNodes,
						'abortOnTooManyNodesFound': autoNetwork.stopConditions.abortOnTooManyNodesFound,
					},
				},
				'returnVerbose': true
			},
		});
	}

	/**
	 * Bonds a node locally
	 * @param addr A requested address for the bonded node. If this parameter equals to 0, then the first free address is assigned to the node.
	 */
	bondLocal(addr) {
		store.dispatch('sendRequest', {
			'mType': 'iqmeshNetwork_BondNodeLocal',
			'data': {
				'repeat': 2,
				'req': {
					'deviceAddr': addr,
				},
				'returnVerbose': true,
			},
		});
	}

	/**
	 * Bonds a node via IQRF Smart Connect
	 * @param addr Address to bond the device to.  If this parameter equals to 0, then the first free address is assigned to the node.
	 * @param scCode Device Smart Connect code
	 * @param testRetries Maximum number of FRCs used to test whether the Node was successfully bonded
	 */
	bondSmartConnect(addr, scCode, testRetries) {
		store.dispatch('sendRequest', {
			'mType': 'iqmeshNetwork_SmartConnect',
			'data': {
				'repeat': 2,
				'req': {
					'deviceAddr': addr,
					'smartConnectCode': scCode,
					'bondingTestRetries': testRetries,
				},
				'returnVerbose': true,
			},
		});
	}

	/**
	 * Clears all bonds
	 * @param coordinatorOnly Removes bonds only in the coordinator memory
	 */
	clearAllBonds(coordinatorOnly) {
		if (coordinatorOnly) {
			store.dispatch('sendRequest', {
				'mType': 'iqrfEmbedCoordinator_ClearAllBonds',
				'data': {
					'req': {
						'nAdr': 0,
						'param': {},
					},
					'returnVerbose': true,
				},
			});
		} else {
			this.removeBond(255, false);
		}
	}

	/**
	 * Performs Coordinator discovery
	 * @param txPower TX Power
	 * @param maxAddr Maximum node address
	 */
	discovery(txPower, maxAddr) {
		store.dispatch('sendRequest', {
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
		});
	}

	/**
	 * Performs device enumeration
	 * @param address Device address
	 */
	enumerateDevice(address) {
		store.dispatch('sendRequest', {
			'mType': 'iqmeshNetwork_EnumerateDevice',
			'data': {
				'repeat': 2,
				'req': {
					'deviceAddr': address,
					'morePeripheralsInfo': true,
				},
				'returnVerbose': true,
			},
		});
	}

	/**
	 * Retrieves list of bonded devices
	 */
	getBonded() {
		store.dispatch('sendRequest', {
			'mType': 'iqrfEmbedCoordinator_BondedDevices',
			'data': {
				'req': {
					'nAdr': 0,
					'param': {},
				},
				'returnVerbose': true,
			},
		});
	}

	/**
	 * Retrieves list of discovered devices
	 */
	getDiscovered() {
		store.dispatch('sendRequest', {
			'mType': 'iqrfEmbedCoordinator_DiscoveredDevices',
			'data': {
				'req': {
					'nAdr': 0,
					'param': {},
				},
				'returnVerbose': true,
			},
		});
	}

	/**
	 * Perform FRC Ping
	 */
	ping() {
		store.dispatch('sendRequest', {
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
		});
	}

	/**
	 * Removes a bond
	 * @param addr Address of a node bond to be removed
	 * @param coordinatorOnly Removes a bond only in the coordinator memory
	 */
	removeBond(addr, coordinatorOnly) {
		if (coordinatorOnly) {
			store.dispatch('sendRequest', {
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
			});
		} else {
			store.dispatch('sendRequest', {
				'mType': 'iqmeshNetwork_RemoveBond',
				'data': {
					'repeat': 2,
					'req': {
						'deviceAddr': addr,
					},
					'returnVerbose': true,
				},
			});
		}
	}

	/**
	 * Sends JSON API request
	 * @param json JSON API request string
	 */
	sendJson(json) {
		store.dispatch('sendRequest', json);
	}

	/**
	 * Writes TR configuration
	 * @param address Device address to write the configuration to
	 * @param configuration New TR configuration
	 */
	writeTrConfiguration(address, configuration) {
		delete configuration.rfBand;
		configuration.deviceAddr = address;
		store.dispatch('sendRequest', {
			'mType': 'iqmeshNetwork_WriteTrConf',
			'data': {
				'repeat': 2,
				'req': configuration,
				'returnVerbose': true,
			},
		});
	}
}

export default new IqrfNetService();
