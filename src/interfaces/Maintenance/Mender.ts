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
import {MenderProtocols, MountModes} from '@/enums/Maintenance/Mender';

/**
 * Mender configuration interface
 */
export interface IMenderConfig {
	/**
	 * Inventory poll interval in seconds
	 */
	InventoryPollIntervalSeconds: number

	/**
	 * Retry poll interval in seconds
	 */
	RetryPollIntervalSeconds: number

	/**
	 * Server
	 */
	ServerURL: string

	/**
	 * Path to server certificate
	 */
	ServerCertificate?: string

	/**
	 * Tenant token
	 */
	TenantToken: string

	/**
	 * Update poll interval in seconds
	 */
	UpdatePollIntervalSeconds: number

	/**
	 * Client protocol
	 */
	ClientProtocol: MenderProtocols
}

/**
 * Filesystem remount interface
 */
export interface IRemount {
	/**
	 * Mode to remount filesystem with
	 */
	mode: MountModes
}