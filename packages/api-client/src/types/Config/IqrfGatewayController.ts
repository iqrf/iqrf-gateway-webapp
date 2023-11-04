/**
 * Copyright 2023-2024 MICRORISC s.r.o.
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

import { type MappingDeviceType } from './Mapping';

/**
 * IQRF Gateway Controller mapping configuration
 */
export interface IqrfGatewayControllerMapping {
	/// Action button GPIO pin
	button: number;
	/// Device type
	deviceType: MappingDeviceType;
	/// Green LED GPIO pin
	greenLed: number;
	/// Mapping ID
	id?: number;
	/// Mapping name
	name: string;
	/// Red LED GPIO pin
	redLed: number;
	///I2C clock GPIO pin
	sck?: number;
	///I2C data GPIO pin
	sda?: number;
}

/**
 * IQRF Gateway Controller button actions
 */
export enum IqrfGatewayControllerAction {
	/// Run autonetwork
	Autonetwork = 'autoNetwork',
	/// Run discovery
	Discovery = 'discovery',
	/// Do nothing
	None = '',
}

/**
 * IQRF Gateway Controller Autonetwork action stop conditions configuration
 */
export interface IqrfGatewayControllerApiAutonetworkStopConditionsConfig {
	/// Abort if network does not have room for new nodes
	abortOnTooManyNodesFound: boolean;
	/// Number of empty waves
	emptyWaves: number;
	/// Total number of waves
	waves: number;
}

/**
 * IQRF Gateway Controller Autonetwork action configuration
 */
export interface IqrfGatewayControllerApiAutonetworkConfig {
	/// Number of retry requests in case of failure
	actionRetries: number;
	/// Run discovery before starting autonetwork
	discoveryBeforeStart: boolean;
	/// Discovery TX power
	discoveryTxPower: number;
	/// Verbose response
	returnVerbose: boolean;
	/// Skip discovery in each wave
	skipDiscoveryEachWave: boolean;
	/// Stop conditions
	stopConditions: IqrfGatewayControllerApiAutonetworkStopConditionsConfig;
}

/**
 * IQRF Gateway Controller Discovery conditions configuration
 */
export interface IqrfGatewayControllerApiDiscoveryConfig {
	/// Highest address to include in discovery
	maxAddr: number;
	/// Verbose response
	returnVerbose: boolean;
	/// TX power
	txPower: number;
}

/**
 * IQRF Gateway Controller actions configuration
 */
export interface IqrfGatewayControllerApiConfig {
	autoNetwork: IqrfGatewayControllerApiAutonetworkConfig;
	discovery: IqrfGatewayControllerApiDiscoveryConfig;
}

/**
 * IQRF Gateway Controller factory reset configuration
 */
export interface IqrfGatewayControllerFactoryResetConfig {
	/// Reset coordinator
	coordinator: boolean;
	/// Reset daemon configuration
	daemon: boolean;
	/// Reset iqaros
	iqaros?: boolean;
	/// Reset network configuration
	network: boolean;
	/// Reset webapp
	webapp: boolean;
}

/**
 * IQRF Gateway Controller logging severity levels
 */
export enum IqrfGatewayControllerLoggingSeverity {
	/// Debug
	Debug = 'debug',
	/// Error
	Error = 'error',
	/// Information
	Info = 'info',
	/// Trace
	Trace = 'Trace',
	/// Warning
	Warning = 'Warning',
}

/**
 * IQRF Gateway Controller logging sinks configuration
 */
export interface IqrfGatewayControllerLoggignSinksConfig {
	/// Log to file
	file: boolean;
	/// Log to syslog
	syslog: boolean;
}

/**
 * IQRF Gateway Controller logging configuration
 */
export interface IqrfGatewayControllerLoggingConfig {
	/// Path to log file
	filePath: string;
	/// Logging severity
	severity: IqrfGatewayControllerLoggingSeverity;
	/// Logging sinks
	sinks: IqrfGatewayControllerLoggignSinksConfig;
}

/**
 * IQRF Gateway Controller power options configuration
 */
export interface IqrfGatewayControllerPowerConfig {
	/// I2C clock GPIO pin
	sck: number;
	/// I2C data GPIO pin
	sda: number;
}

/**
 * IQRF Gateway Controller action button configuration
 */
export interface IqrfGatewayControllerActionButtonConfig {
	/// Action to invoke
	api: IqrfGatewayControllerAction;
	/// Action button GPIO pin
	button: number;
}

/**
 * IQRF Gateway Controller status led configuration
 */
export interface IqrfGatewayControllerStatusLedConfig {
	/// Green LED GPIO pin
	greenLed: number;
	/// Red LED GPIO pin
	redLed: number;
}

/**
 * IQRF Gateway Controller websocket configuration
 */
export interface IqrfGatewayControllerWsConfig {
	/// Daemon API websocket URL
	api: string;
	/// Daemon monitor websocket URL
	monitor: string;
}

/**
 * IQRF Gateway Controller configuration
 */
export interface IqrfGatewayControllerConfig {
	/// Daemon API configuration
	daemonApi: IqrfGatewayControllerApiConfig;
	/// Factory reset configuration
	factoryReset: IqrfGatewayControllerFactoryResetConfig;
	/// Logging configuration
	logger: IqrfGatewayControllerLoggingConfig;
	/// Power off configuration
	powerOff: IqrfGatewayControllerPowerConfig;
	/// Action button configuration
	resetButton: IqrfGatewayControllerActionButtonConfig;
	/// Status LED configuration
	statusLed: IqrfGatewayControllerStatusLedConfig;
	/// Websocket configuration
	wsServers: IqrfGatewayControllerWsConfig;
}
