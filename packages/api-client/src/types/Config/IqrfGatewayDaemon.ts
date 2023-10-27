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

/**
 * IQRF Gateway Daemon component names
 */
export enum IqrfGatewayDaemonComponentName {
	/// IQRF IDE counterpart component
	IqrfIdeCounterpart = 'iqrf::IdeCounterpart',
	/// IQRF Gateway Daemon JSON splitter component
	IqrfJsonSplitter = 'iqrf::JsonSplitter',
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
 * IQRF Gateway Daemon component instance configurations
 */
export interface IqrfGatewayDaemonComponentInstanceConfigurations {
	[IqrfGatewayDaemonComponentName.IqrfIdeCounterpart]: IqrfGatewayDaemonIdeCounterpart;
	[IqrfGatewayDaemonComponentName.IqrfJsonSplitter]: IqrfGatewayDaemonJsonSplitter;
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
