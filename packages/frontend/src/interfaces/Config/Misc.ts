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

import {ComponentInstanceBase} from './Daemon';
import {IWsService} from './Messaging';
import {RequiredInterface} from './RequiredInterfaces';

/**
 * Monitor websocket service component interface
 */
export interface IMonitorComponent {
	/**
	 * Monitor instance
	 */
	monitor: IMonitorInstance;

	/**
	 * Websocket service interface
	 */
	webSocket: IWsService;
}

/**
 * Monitor service component instance inteface
 */
export interface IMonitorInstance extends ComponentInstanceBase {
	/**
	 * Monitoring report period
	 */
	reportPeriod: number;

	/**
	 * Required interfaces
	 */
	RequiredInterfaces: Array<RequiredInterface>;
}

/**
 * Monitor service websocket interface
 */
export interface IMonitorWs {
	/**
	 * Instance name
	 */
	instance: string

	/**
	 * Port
	 */
	WebsocketPort: number

	/**
	 * Accept only requests from localhost
	 */
	acceptOnlyLocalhost: boolean

	/**
	 * Use TLS
	 */
	tlsEnabled?: boolean

	/**
	 * TLS mode
	 */
	tlsMode?: string

	/**
	 * Path to certificate file
	 */
	certificate?: string

	/**
	 * Path to private key file
	 */
	privateKey?: string
}

/**
 * IQRF Repository component instance interface
 */
export interface IIqrfRepository extends ComponentInstanceBase {
	/**
	 * Repository URL
	 */
	urlRepo: string

	/**
	 * Check period in minutes
	 */
	checkPeriodInMinutes: number

	/**
	 * Download date if repository cache is empty?
	 */
	downloadIfRepoCacheEmpty: boolean
}

/**
 * IQRF repository access configuration
 */
export interface IIqrfRepositoryConfig {
	/**
	 * Repository API endpoint
	 */
	apiEndpoint: string

	/**
	 * Repository credentials
	 */
	credentials: IIqrfRepositoryCredentials
}

/**
 * IQRF Repository credentials
 */
export interface IIqrfRepositoryCredentials {
	/**
	 * Username
	 */
	username: string|null

	/**
	 * Password
	 */
	password: string|null
}

/**
 * Syslog logging service component instance interface
 */
export interface ISyslogLogger extends ComponentInstanceBase {

	/**
	 * Array of verbosity levels for different channels
	 */
	VerbosityLevels: Array<ITraceVerbosityLevel>

}

/**
 * Logging service component instance interface
 */
export interface ITraceService extends ComponentInstanceBase {
	/**
	 * Name of log file
	 */
	filename: string

	/**
	 * Maximum log file size
	 */
	maxSizeMB: number

	/**
	 * Maximum lifespan of timestamped files in minutes (Daemon version >= 2.3.0)
	 */
	maxAgeMinutes: number

	/**
	 * Maximum number of timestamped files (Daemon version >= 2.3.0)
	 */
	maxNumber: number

	/**
	 * Path to directory with log files
	 */
	path: string

	/**
	 * Should log files be timestamped?
	 */
	timestampFiles: boolean

	/**
	 * Array of verbosity levels for different channels
	 */
	VerbosityLevels: Array<ITraceVerbosityLevel>
}

/**
 * VerbosityLevels interface for Logging service component instance
 */
export interface ITraceVerbosityLevel {
	/**
	 * Verbosity level channel
	 */
	channel: number

	/**
	 * Verbosity severity
	 */
	level: string
}

/**
 * OTA upload configuration instance interface
 */
export interface IOtaUploadConfig {
	/**
	 * Component name
	 */
	component: string

	/**
	 * Component instance name
	 */
	instance: string

	/**
	 * Upload path
	 */
	uploadPath: string

	/**
	 * Upload suffix
	 */
	uploadPathSuffix: string
}

/**
 * IQRF Info component instance interface
 */
export interface IIqrfInfo {
	/**
	 * Component name
	 */
	component: string

	/**
	 * Component instance name
	 */
	instance: string

	/**
	 * Enumerate network after startup?
	 */
	enumAtStartUp: boolean

	/**
	 * Enumeration period in minutes
	 */
	enumPeriod: number

	/**
	 * Uniform DPA version and OS build according to coordinator?
	 */
	enumUniformDpaVer: boolean

	/**
	 * Include metadata in messages?
	 */
	metaDataToMessages?: boolean
}
