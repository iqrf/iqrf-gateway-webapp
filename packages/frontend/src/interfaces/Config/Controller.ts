/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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

import {ConfigDeviceType} from '@/enums/Config/ConfigurationProfiles';

/**
 * IQRF GW Controller configuration interface
 */
export interface IController {
	/**
	 * Daemon API configuration
	 */
	daemonApi: IControllerDaemonApi

	/**
	 * Factory reset configuration
	 */
	factoryReset: IControllerFactoryReset

	/**
	 * Logger configuration
	 */
	logger: IControllerLogger

	/**
	 * I2C pin configuration
	 */
	powerOff?: IControllerPowerOff

	/**
	 * Reset button configuration
	 */
	resetButton: IControllerResetButton

	/**
	 * Status LED configuraiton
	 */
	statusLed: IControllerStatusLed

	/**
	 * WebSocket servers configuraiton
	 */
	wsServers: IControllerWsServers
}

/**
 * Controller AutoNetwork API call stop conditions configuration interface
 */
interface IControllerAutoNetworkStopConditions {
	/**
	 * Abort AutoNetwork if too many nodes were found
	 */
	abortOnTooManyNodesFound: boolean

	/**
	 * Maximum number of empty consecutive waves
	 */
	emptyWaves: number

	/**
	 * Maximum number of waves
	 */
	waves: number
}

/**
 * Controller AutoNetwork API call configuration interface
 */
interface IControllerAutoNetwork {
	/**
	 * Number of retry transactions
	 */
	actionRetries: number

	/**
	 * Run discovery before AutoNetwork starts
	 */
	discoveryBeforeStart: boolean

	/**
	 * Discovery TX power
	 */
	discoveryTxPower: number

	/**
	 * Verbose response
	 */
	returnVerbose: boolean

	/**
	 * Skip discovery each AutoNetwork wave
	 */
	skipDiscoveryEachWave: boolean

	/**
	 * AutoNetwork stop conditions configuration
	 */
	stopConditions: IControllerAutoNetworkStopConditions
}

/**
 * Controller Discovery API call configuration interface
 */
interface IControllerDiscovery {
	/**
	 * Maximum address included in discovery process
	 */
	maxAddr: number

	/**
	 * Verbose response
	 */
	returnVerbose: boolean

	/**
	 * TX power
	 */
	txPower: number
}

/**
 * Controller Daemon API call configuration interface
 */
interface IControllerDaemonApi {
	/**
	 * AutoNetwork API call configuration
	 */
	autoNetwork: IControllerAutoNetwork

	/**
	 * Discovery API call configuration
	 */
	discovery: IControllerDiscovery
}

/**
 * Controller factory reset configuration interface
 */
interface IControllerFactoryReset {
	/**
	 * Reset IQRF Cloud Provisioning
	 */
	cloudProv?: boolean

	/**
	 * Reset coordinator
	 */
	coordinator: boolean

	/**
	 * Reset IQRF GW Daemon
	 */
	daemon: boolean

	/**
	 * Reset IQAROS
	 */
	iqaros?: boolean

	/**
	 * Reset network
	 */
	network: boolean

	/**
	 * Reset IQRF GW Webapp
	 */
	webapp: boolean
}

/**
 * Controller logger configuration interface
 */
interface IControllerLogger {
	/**
	 * Path to logging file
	 */
	filePath: string

	/**
	 * Logging severity level
	 */
	severity: string

	/**
	 * Logging sinks
	 */
	sinks: IControllerLoggerSinks
}

/**
 * Controller logger sinks configuration interface
 */
interface IControllerLoggerSinks {
	/**
	 * Log to file
	 */
	file: boolean

	/**
	 * Log to syslog
	 */
	syslog: boolean
}

/**
 * Controller I2C pin configuration interface
 */
interface IControllerPowerOff {
	/**
	 * I2C clock pin number
	 */
	sck: number

	/**
	 * I2C data pin number
	 */
	sda: number
}

/**
 * Controller reset button configuration interface
 */
interface IControllerResetButton {
	/**
	 * Daemon API call to be executed
	 */
	api: string

	/**
	 * Reset button GPIO pin number
	 */
	button: number
}

/**
 * Controller configuration status led interface
 */
interface IControllerStatusLed {
	/**
	 * Green LED GPIO pin number
	 */
	greenLed: number

	/**
	 * Red LED GPIO pin number
	 */
	redLed: number
}

/**
 * Controller configuration websocket servers interface
 */
interface IControllerWsServers {
	/**
	 * Daemon API WebSocket server address
	 */
	api: string

	/**
	 * Daemon monitor WebSocket server address
	 */
	monitor: string
}

/**
 * Controller pin configuration profile interface
 */
export interface IControllerPinConfig {
	/**
	 * Profile ID
	 */
	id?: number

	/**
	 * Profile name
	 */
	name: string

	/**
	 * Device type
	 */
	deviceType: ConfigDeviceType

	/**
	 * Green LED pin number
	 */
	greenLed: number

	/**
	 * Red LED pin number
	 */
	redLed: number

	/**
	 * API button pin number
	 */
	button: number

	/**
	 * I2C clock pin number
	 */
	sck?: number

	/**
	 * I2C data pin number
	 */
	sda?: number
}
