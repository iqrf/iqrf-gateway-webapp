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
 * Mender client configuration interface
 */
export interface MenderClientConfig {
	/// Inventory poll interval in seconds
	InventoryPollIntervalSeconds: number;
	/// Retry poll interval in seconds
	RetryPollIntervalSeconds: number;
	/// Path to certificate
	ServerCertificate: string;
	/// Mender service server address
	ServerURL: string;
	/// Tenant ownership token
	TenantToken: string;
	/// Update poll interval in seconds
	UpdatePollIntervalSeconds: number;
}

/**
 * Mender connect configuration interface
 */
export interface MenderConnectConfig {
	/// File transfer feature enabled
	FileTransfer: boolean;
	/// Port forwarding feature enabled
	PortForward: boolean;
}

/**
 * Mender configuration interface
 */
export interface MenderConfig {
	/// mender-client configuration
	client: MenderClientConfig;
	/// mender-connect configuration
	connect: MenderConnectConfig;
}

/**
 * Filesystem mount modes
 */
export enum MenderMountMode {
	/// Read-only
	RO = 'ro',
	/// Read-write
	RW = 'rw'
}

/**
 * Filesystem remount interface
 */
export interface MenderRemount {
	/// mountMode
	mode: MenderMountMode;
}
