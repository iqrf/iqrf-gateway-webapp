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

import {FrcCommands} from '@/enums/IqrfNet/Maintenance';

/**
 * Maintenance FRC Response Time result interface
 */
export interface IFrcResponseTimeResult {
	/**
	 * FRC command
	 */
	command: FrcCommands

	/**
	 * FRC response time node results
	 */
	nodes: Array<IFrcResponseTimeResultNodes>

	/**
	 * Number of nodes that did not respond
	 */
	inaccessibleNodes: number

	/**
	 * Number of nodes that did not handle FRC response time event
	 */
	unhandledNodes: number

	/**
	 * Current FRC response time at [C]
	 */
	currentResponseTime: number

	/**
	 * Recommended FRC response time
	 */
	recommendedResponseTime: number
}

/**
 * Maintenance FRC Response Time nodes results interface
 */
export interface IFrcResponseTimeResultNodes {
	/**
	 * Device address
	 */
	deviceAddr: number

	/**
	 * Did node respond?
	 */
	responded: boolean

	/**
	 * Did node handle FRC response time event?
	 */
	handled?: boolean

	/**
	 * Node FRC response time
	 */
	responseTime?: number
}

/**
 * Maintenance RF Signal Test parameters interface
 */
export interface IRfSignalTestParams {
	/**
	 * Device address
	 */
	deviceAddr: number

	/**
	 * RF channel
	 */
	rfChannel: number

	/**
	 * RX filter
	 */
	rxFilter: number

	/**
	 * Measurement time
	 */
	measurementTime: number
}

/**
 * Maintenance RF Signal Test result interface
 */
export interface IRfSignalTestResult {
	/**
	 * Device address
	 */
	deviceAddr: number

	/**
	 * Is device online?
	 */
	online: boolean
}
