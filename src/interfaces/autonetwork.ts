/**
 * Copyright 2017-2022 IQRF Tech s.r.o.
 * Copyright 2019-2022 MICRORISC s.r.o.
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
/**
 * AutoNetwork base interface
 */
export interface AutoNetworkBase {
	/**
	 * Number of DPA retry transactions if the original transaction fails
	 */
	actionRetries: number

	/**
	 * Run discovery before starting the autonetwork process
	 */
	discoveryBeforeStart: boolean

	/**
	 * TX power
	 */
	discoveryTxPower: number

	/**
	 * Skip discovery during each wave
	 */
	skipDiscoveryEachWave: boolean

	/**
	 * Unbond nodes that do not respond
	 */
	unbondUnrespondingNodes: boolean

	/**
	 * Skip SmartConnect pre-bonding step
	 */
	skipPrebonding: boolean
}

/**
 * AutoNetwork overlapping networks interface
 */
export interface AutoNetworkOverlappingNetworks {
	/**
	 * Network number
	 */
	network: number

	/**
	 * Number of networks
	 */
	networks: number
}

/**
 * AutoNetwork stop conditions interface
 */
export interface AutoNetworkStopConditions {
	/**
	 * Abort if too many nodes are found
	 */
	abortOnTooManyNodesFound: boolean

	/**
	 * Abort after number of consecutive waves where no nodes are found
	 */
	emptyWaves: number

	/**
	 * Abort after number of nodes is found
	 */
	nodeCount: number

	/**
	 * Abort after number of waves
	 */
	waves: number
}

