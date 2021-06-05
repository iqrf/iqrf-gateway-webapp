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
/**
 * AutoNetwork base interface
 */
export interface AutoNetworkBase {
	actionRetries: number
	discoveryBeforeStart: boolean
	discoveryTxPower: number
	skipDiscoveryEachWave: boolean
}

/**
 * AutoNetwork overlapping networks interface
 */
export interface AutoNetworkOverlappingNetworks {
	network: number
	networks: number
}

/**
 * AutoNetwork stop conditions interface
 */
export interface AutoNetworkStopConditions {
	abortOnTooManyNodesFound: boolean
	emptyWaves: number
	nodeCount: number
	waves: number
}

