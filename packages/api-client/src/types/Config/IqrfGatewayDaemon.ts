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
 * IQRF Gateway Daemon component configuration
 */
export interface IqrfGatewayDaemonComponent {
	/// Component enabled
	enabled: boolean;
	/// Library file name
	libraryName: string;
	/// Path to library directory
	libraryPath: string;
	/// Component name
	name: string;
	/// Launch priority
	startLevel: number;
}

/**
 * Iqrf Gateway Daemon component instance configuration base
 */
export interface IqrfGatewayDaemonComponentInstanceBase {
	/// Component name
	component: string;
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

export interface IqrfGatewayDaemonIdeCounterpart extends IqrfGatewayDaemonComponentInstanceBase {
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
