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

import {ComponentInstanceBase} from '../daemonComponent';
import {IWsService} from './Messaging';
import {RequiredInterface} from '../requiredInterfaces';

/**
 * Monitor service component instance
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
 * Monitor websocket service component interface
 */
export interface IMonitorWsInstance {
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
 * Daemon API monitor message
 */
export interface IMonitorMsg {
	/**
	 * Message type
	 */
	mType: string;

	/**
	 * Message data
	 */
	data: IMonitorMsgData;
}

/**
 * Monitor message data
 */
interface IMonitorMsgData {
	/**
	 * Message number
	 */
	num: number;

	/**
	 * UNIX timestamp
	 */
	timestamp: number;

	/**
	 * DPA queue length
	 */
	dpaQueueLen: number;

	/**
	 * IQRF channel state
	 */
	iqrfChannelState: string;

	/**
	 * DPA channel state
	 */
	dpaChannelState: string;

	/**
	 * Message queue length
	 */
	msgQueueLen: number;

	/**
	 * Operation mode
	 */
	operMode: string;
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
