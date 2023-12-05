/**
 * Copyright 2023 MICRORISC s.r.o.
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

import { type MappingDeviceType, type MappingType } from './Mapping';

/**
 * IQRF Gateway Daemon component names
 */
export enum IqrfGatewayDaemonComponentName {
	/// IQRF CDC component
	IqrfCdc = 'iqrf::IqrfCdc',
	/// IQRF DB component
	IqrfDb = 'iqrf::IqrfDb',
	/// IQRF DPA component
	IqrfDpa = 'iqrf::IqrfDpa',
	/// IQRF IDE counterpart component
	IqrfIdeCounterpart = 'iqrf::IdeCounterpart',
	/// JS Cache component
	IqrfJsCache = 'iqrf::JsCache',
	/// JSON DPA API RAW component
	IqrfJsonDpaApiRaw = 'iqrf::JsonDpaApiRaw',
	/// JSON splitter component
	IqrfJsonSplitter = 'iqrf::JsonSplitter',
	/// MQTT messaging component
	IqrfMqttMessaging = 'iqrf::MqttMessaging',
	/// IQRF SPI component
	IqrfSpi = 'iqrf::IqrfSpi',
	/// IQRF UART component
	IqrfUart = 'iqrf::IqrfUart',
	/// UDP messaging component
	IqrfUdpMessaging = 'iqrf::UdpMessaging',
	/// Shape Trace file service component
	ShapeTraceFile = 'shape::TraceFileService',
}

/**
 * IQRF Gateway Daemon component configuration
 */
export interface IqrfGatewayDaemonComponentConfiguration<C extends IqrfGatewayDaemonComponentName> {
	/// Component enabled
	enabled: boolean;
	/// Library file name
	libraryName: string;
	/// Path to library directory
	libraryPath: string;
	/// Component name
	name: C;
	/// Launch priority
	startLevel: number;
}

/**
 * Iqrf Gateway Daemon component instance configuration base
 */
export interface IqrfGatewayDaemonComponentInstanceBase<C extends IqrfGatewayDaemonComponentName> {
	/// Component name
	component: C;
	/// Instance name
	instance: string;
}

/**
 * IQRF Gateway Daemon IqrfCdc component configuration
 */
export interface IqrfGatewayDaemonCdc extends IqrfGatewayDaemonComponentInstanceBase<IqrfGatewayDaemonComponentName.IqrfCdc> {
	/// Device name
	IqrfInterface: string;
}

/**
 * IQRF Gateway Daemon IqrfDb component configuration
 */
export interface IqrfGatewayDaemonDb extends IqrfGatewayDaemonComponentInstanceBase<IqrfGatewayDaemonComponentName.IqrfDb> {
	/// Enumerate automatically without prior invocation
	autoEnumerateBeforeInvoked: boolean;
	/// Run enumeration on launch
	enumerateOnLaunch: boolean;
	/// Include metadata in responses
	metadataToMessages: boolean;
}

/**
 * IQRF Gateway Daemon IqrfDpa component configuration
 */
export interface IqrfGatewayDaemonDpa extends IqrfGatewayDaemonComponentInstanceBase<IqrfGatewayDaemonComponentName.IqrfDpa> {
	/// DPA request timeout
	DpaHandlerTimeout: number;
}

/**
 * IQRF Gateway Daemon IdeCounterpart operation modes
 */
export enum IqrfGatewayDaemonIdeCounterpartMode {
	Forwarding = 'forwarding',
	Operational = 'operational',
	Service = 'service',
}

/**
 * IQRF Gateway Daemon IdeCounterpart component configuration
 */
export interface IqrfGatewayDaemonIdeCounterpart extends IqrfGatewayDaemonComponentInstanceBase<IqrfGatewayDaemonComponentName.IqrfIdeCounterpart> {
	/// Gateway identification IP stack version
	gwIdentIpStack: string;
	/// Gateway identification mode byte
	gwIdentModeByte: number;
	/// Gateway identification name
	gwIdentName: string;
	/// Gateway identification net bios
	gwIdentNetBios: string;
	/// Gateway identification public IP
	gwIdentPublicIp: string;
	/// Startup operation mode
	operMode?: IqrfGatewayDaemonIdeCounterpartMode;
}

/**
 * IQRF Gateway Daemon JsCache component configuration
 */
export interface IqrfGatewayDaemonJsCache extends IqrfGatewayDaemonComponentInstanceBase<IqrfGatewayDaemonComponentName.IqrfJsCache> {
	/// Cache update check period
	checkPeriodInMinutes: number;
	/// Download repository cache if empty
	downloadIfRepoCacheEmpty: boolean;
	/// IQRF repository URL
	urlRepo: string;
}

/**
 * IQRF Gateway Daemon JsonDpaApiRaw component configuration
 */
export interface IqrfGatewayDaemonJsonDpaApiRaw extends IqrfGatewayDaemonComponentInstanceBase<IqrfGatewayDaemonComponentName.IqrfJsonDpaApiRaw> {
	/// Allow asynchronous DPA messages
	asyncDpaMessage: boolean;
}

/**
 * IQRF Gateway Daemon JsonSplitter component configuration
 */
export interface IqrfGatewayDaemonJsonSplitter extends IqrfGatewayDaemonComponentInstanceBase<IqrfGatewayDaemonComponentName.IqrfJsonSplitter> {
	/// Instance ID
	insId: string;
	/// List of messaging service instances
	messagingList: string[];
	/// Validate outgoing responses
	validateJsonResponse: boolean;
}

/**
 * IQRF Gateway Daemon MqttMessaging persistence options
 */
export enum IqrfGatewayDaemonMqttMessagingPersistence {
	/// Memory-based
	MEMORY = 0,
	/// Filesystem-based
	FILESYSTEM = 1,
	/// Application-based
	APPLICATION = 2,
}

/**
 * IQRF Gateway Daemon MqttMessaging qos options
 */
export enum IqrfGatewayDaemonMqttMessagingQos {
	/// At most once
	AT_MOST_ONCE = 0,
	/// At least once
	AT_LEAST_ONCE = 1,
	/// Exactly once
	ONCE = 2,
}

/**
 * IQRF Gateway Daemon MqttMessaging component configuration
 */
export interface IqrfGatewayDaemonMqttMessaging extends IqrfGatewayDaemonComponentInstanceBase<IqrfGatewayDaemonComponentName.IqrfMqttMessaging> {
	/// Broker URL
	BrokerAddr: string;
	/// Client ID
	ClientId: string;
	/// Connection timeout
	ConnectTimeout: number;
	/// Authenticate certificate server-side
	EnableServerCertAuth: boolean;
	/// Cipher suites
	EnabledCipherSuites: string;
	/// TLS enabled
	EnabledSSL: boolean;
	/// Keep-alive interval
	KeepAliveInterval: number;
	/// Path to key store
	KeyStore: string;
	/// Maximum reconnect delay
	MaxReconnect: number;
	/// Minimum reconnect delay
	MinReconnect: number;
	/// Client password
	Password: string;
	/// Persistence option
	Persistence: IqrfGatewayDaemonMqttMessagingPersistence;
	/// Path to private key
	PrivateKey: string;
	/// Private key password
	PrivateKeyPassword: string;
	/// QoS level
	Qos: IqrfGatewayDaemonMqttMessagingQos;
	/// Request topic
	TopicRequest: string;
	/// Response topic
	TopicResponse: string;
	/// Path to trust store
	TrustStore: string;
	/// Client username
	User: string;
	/// Allow asynchronous messages
	acceptAsyncMsg: boolean;
}

/**
 * IQRF Gateway Daemon IqrfSpi component configuration
 */
export interface IqrfGatewayDaemonSpi extends IqrfGatewayDaemonComponentInstanceBase<IqrfGatewayDaemonComponentName.IqrfSpi> {
	/// Device name
	IqrfInterface: string;
	/// BUS enable GPIO pin
	busEnableGpioPin: number;
	/// I2C enable GPIO pin
	i2cEnableGpioPin?: number;
	/// Programming mode switch GPIO pin mode
	pgmSwitchGpioPin: number;
	/// Power enable GPIO pin
	powerEnableGpioPin: number;
	/// SPI enable GPIO pin
	spiEnableGpioPin?: number;
	/// Reset SPI on start
	spiReset: boolean;
	/// UART enable GPIO pin
	uartEnableGpioPin?: number;
}

/**
 * IQRF Gateway Daemon IqrfUart component configuration
 */
export interface IqrfGatewayDaemonUart extends IqrfGatewayDaemonComponentInstanceBase<IqrfGatewayDaemonComponentName.IqrfUart> {
	/// Device name
	IqrfInterface: string;
	/// UART baud rate
	baudRate: number;
	/// BUS enable GPIO pin
	busEnableGpioPin: number;
	/// I2C enable GPIO pin
	i2cEnableGpioPin?: number;
	/// Programming mode switch GPIO pin mode
	pgmSwitchGpioPin: number;
	/// Power enable GPIO pin
	powerEnableGpioPin: number;
	/// SPI enable GPIO pin
	spiEnableGpioPin?: number;
	/// UART enable GPIO pin
	uartEnableGpioPin?: number;
	/// Reset UART on start
	uartReset: boolean;
}

/**
 * IQRF Gateway Daemon UdpMessaging component configuration
 */
export interface IqrfGatewayDaemonUdpMessaging extends IqrfGatewayDaemonComponentInstanceBase<IqrfGatewayDaemonComponentName.IqrfUdpMessaging> {
	/// Local port number
	LocalPort: number;
	/// Remote port number
	RemotePort: number;
	/// UDP client records expiration time
	deviceRecordExpiration: number;
}

/**
 * Shape trace verbosity
 */
export enum ShapeTraceVerbosity {
	/// Debug
	Debug = 'DBG',
	/// Error
	Error = 'Err',
	/// Information
	Info = 'INF',
	/// Warning
	Warning = 'WAR'
}

/**
 * Shape trace channel verbosity interface
 */
export interface ShapeTraceChannelVerbosity {
	/// Tracing channel
	channel: number;
	/// Logging severity
	level: ShapeTraceVerbosity;
}

/**
 * Shape trace file service interface
 */
export interface ShapeTraceFileService extends IqrfGatewayDaemonComponentInstanceBase<IqrfGatewayDaemonComponentName.ShapeTraceFile> {
	/// Array of verbosity levels for different channels
	VerbosityLevels: ShapeTraceChannelVerbosity[];
	/// Name of log file
	filename: string;
	/// Maximum lifespan of timestamped files in minutes (Daemon version >= 2.3.0)
	maxAgeMinutes: number;
	/// Maximum number of timestamped files (Daemon version >= 2.3.0)
	maxNumber: number;
	/// Maximum log file size
	maxSizeMB: number;
	/// Path to directory with log files
	path: string;
	/// Should log files be timestamped?
	timestampFiles: boolean;
}

/**
 * IQRF Gateway Daemon component instance configurations
 */
export interface IqrfGatewayDaemonComponentInstanceConfigurations {
	[IqrfGatewayDaemonComponentName.IqrfCdc]: IqrfGatewayDaemonCdc;
	[IqrfGatewayDaemonComponentName.IqrfDpa]: IqrfGatewayDaemonDpa;
	[IqrfGatewayDaemonComponentName.IqrfDb]: IqrfGatewayDaemonDb;
	[IqrfGatewayDaemonComponentName.IqrfIdeCounterpart]: IqrfGatewayDaemonIdeCounterpart;
	[IqrfGatewayDaemonComponentName.IqrfJsCache]: IqrfGatewayDaemonJsCache;
	[IqrfGatewayDaemonComponentName.IqrfJsonDpaApiRaw]: IqrfGatewayDaemonJsonDpaApiRaw;
	[IqrfGatewayDaemonComponentName.IqrfJsonSplitter]: IqrfGatewayDaemonJsonSplitter;
	[IqrfGatewayDaemonComponentName.IqrfMqttMessaging]: IqrfGatewayDaemonMqttMessaging;
	[IqrfGatewayDaemonComponentName.IqrfSpi]: IqrfGatewayDaemonSpi;
	[IqrfGatewayDaemonComponentName.IqrfUart]: IqrfGatewayDaemonUart;
	[IqrfGatewayDaemonComponentName.IqrfUdpMessaging]: IqrfGatewayDaemonUdpMessaging;
	[IqrfGatewayDaemonComponentName.ShapeTraceFile]: ShapeTraceFileService;
}

/**
 * IQRF Gateway Daemon component instance configuration
 */
export type IqrfGatewayDaemonComponentInstanceConfiguration<C extends IqrfGatewayDaemonComponentName> = IqrfGatewayDaemonComponentInstanceConfigurations[C];

/**
 * IQRF Gateway Daemon component generic
 */
export interface IqrfGatewayDaemonComponent<C extends IqrfGatewayDaemonComponentName> {
	configuration: IqrfGatewayDaemonComponentConfiguration<C>;
	instances: IqrfGatewayDaemonComponentInstanceConfiguration<C>[];
}

/**
 * IQRF Gateway Daemon component state
 */
export interface IqrfGatewayDaemonComponentState {
	/// Component enabled
	enabled: boolean;
	/// Component name
	name: IqrfGatewayDaemonComponentName;
}

/**
 * IQRF Gateway Daemon configuration
 */
export interface IqrfGatewayDaemonConfig {
	/// Application name
	applicationName: string;
	/// Cache directory
	cacheDir: string;
	/// Components
	components: IqrfGatewayDaemonComponentConfiguration<IqrfGatewayDaemonComponentName>[];
	/// Configuration directory
	configurationDir: string;
	/// Data directory
	dataDir: string;
	/// Deployment directory
	deploymentDir: string;
	/// Resource directory
	resourceDir: string;
	/// User directory
	userDir: string;
}

/**
 * IQRF Gateway Daemon mapping
 */
export interface IqrfGatewayDaemonMapping {
	/// Interface device name
	IqrfInterface: string;
	/// UART baud rate
	baudRate?: number;
	/// Bus enable GPIO pin
	busEnableGpioPin: number;
	/// Device type
	deviceType: MappingDeviceType;
	/// I2C enable GPIO pin
	i2cEnableGpioPin?: number;
	/// Profile ID
	id?: number;
	/// Profile name
	name: string;
	/// Programming mode switch GPIO pin
	pgmSwitchGpioPin: number;
	/// Power enable GPIO pin
	powerEnableGpioPin: number;
	/// SPI enable GPIO pin
	spiEnableGpioPin?: number;
	/// Mapping type
	type: MappingType;
	/// UART enable GPIO pin
	uartEnableGpioPin?: number;
}

/**
 * IQRF Gateway Daemon scheduler messaging instances
 */
export interface IqrfGatewayDaemonSchedulerMessagings {
	/// MQTT messaging instances
	mqtt: string[],
	/// WS messaging instances
	ws: string[],
}
