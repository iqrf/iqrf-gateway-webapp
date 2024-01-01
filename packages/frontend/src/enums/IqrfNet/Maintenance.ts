/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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
 * FRC commands
 */
export enum FrcCommands {
	IQRF2BITS = 0x10,
	IQRF1BYTE = 0x90,
	IQRF2BYTE = 0xE0,
	IQRF4BYTE = 0xF9,
	USER2BITS = 0x40,
	USER1BYTE = 0xC0,
	USER2BYTE = 0xF0,
	USER4BYTE = 0xFC,
}

/**
 * RF signal test target
 */
export enum RfSignalTargets {
	COORDINATOR = 'coordinator',
	ALL_NODES = 'allNodes',
}

/**
 * RF signal test measurement times
 */
export enum RfSignalMeasurementTimes {
	MS40 = 40,
	MS360 = 360,
	MS680 = 680,
	MS1320 = 1320,
	MS2600 = 2600,
	MS5160 = 5160,
	MS10280 = 10280,
	MS20620 = 20620,
}

/**
 * Resolvable Network Issues
 */
export enum NetworkIssueTypes {
	INCONSISTENT_MIDS_IN_COORDINATOR = 'inconsistentMidsInCoordinator',
	DUPLICATED_ADDRESSES = 'duplicatedAddresses',
	USELESS_PREBONDED_NODES = 'uselessPrebondedNodes',
}
