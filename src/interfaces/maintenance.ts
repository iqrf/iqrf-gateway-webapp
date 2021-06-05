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
import {MenderProtocols} from '../enums/Maintenance/Mender';

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
	 * Mender server 
	 */
	ServerURL: string

	/**
	 * Mender token
	 */
	TenantToken: string

	/**
	 * Update poll interval in seconds
	 */
	UpdatePollIntervalSeconds: number

	/**
	 * Mender client protocol
	 */
	ClientProtocol: MenderProtocols
}

/**
 * MMonit configuration interface
 */
export interface IMonitConfig {
    /**
     * MMonit username
     */
    username: string

    /**
     * MMonit password
     */
    password: string

    /**
     * MMonit server and endpoint
     */
    endpoint: string
}
