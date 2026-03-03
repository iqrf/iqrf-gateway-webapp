/**
 * Copyright 2023-2026 MICRORISC s.r.o.
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

import { type DateTime } from 'luxon';

/**
 * Daemon API access token states
 */
export enum DaemonApiTokenStatus {
	/**
	 * Valid token
	 */
	Valid,
	/**
	 * Expired token
	 */
	Expired,
	/**
	 * Revoked token
	 */
	Revoked,
}

/**
 * Daemon API access token expiration units
 */
export enum DaemonApiTokenExpirationUnit {
	Day = 'd',
	Week = 'w',
	Month = 'm',
	Year = 'y',
}

/**
 * Daemon API access token create interface
 */
export type DaemonApiTokenCreate =
	| DaemonApiTokenCreateAbsoluteExpiration
	| DaemonApiTokenCreateRelativeExpiration;

/**
 * Daemon API access token create base interface
 */
interface DaemonApiTokenTokenCreateBase {
	/**
	 * Token owner
	 */
	owner: string;
}

/**
 * Daemon API access token relative expiration interface
 */
export interface DaemonApiTokenCreateRelativeExpiration extends DaemonApiTokenTokenCreateBase {

	/**
	 * Time unit
	 */
	unit: DaemonApiTokenExpirationUnit;
	/**
	 * Unit count
	 */
	count: number;
	/**
	 * Expiration forbidden
	 */
	expiration?: never;
}

/**
 * Daemon API access token absolute expiration interface
 */
export interface DaemonApiTokenCreateAbsoluteExpiration extends DaemonApiTokenTokenCreateBase {
	/**
	 * Expiration timestamp
	 */
	expiration: string;
	/**
	 * Time unit forbidden
	 */
	unit?: never;
	/**
	 * Unit count forbidden
	 */
	count?: never;
}

/**
 * Daemon API access token created interface
 */
export interface DaemonApiTokenCreated {
	/**
	 * Token ID
	 */
	id: number;
	/**
	 * Token
	 */
	token: string;
}

/**
 * Daemon API access token interface
 */
export interface DaemonApiToken {
	/**
	 * Token ID
	 */
	id: number;
	/**
	 * Token
	 */
	token: string;
}

/**
 * Daemon API access token information base interface
 */
export interface DaemonApiTokenInfoBase {
	/**
	 * Token ID
	 */
	id: number;
	/**
	 * Token owner
	 */
	owner: string;
	/**
	 * Token status
	 */
	status: DaemonApiTokenStatus;
	/**
	 * Service mode permission
	 */
	service: boolean;
}

/**
 * Daemon API access token information RAW API response interface
 */
export interface DaemonApiTokenInfoRaw extends DaemonApiTokenInfoBase {
	/**
	 * Created timestamp
	 */
	created_at: string;
	/**
	 * Expiration timestamp
	 */
	expires_at: string;
	/**
	 * Invalidated timestamp
	 */
	invalidated_at: string|null;
}

/**
 * Daemon API access token information parsed interface
 */
export interface DaemonApiTokenInfo extends DaemonApiTokenInfoBase {
	/**
	 * Created timestamp
	 */
	created_at: DateTime;
	/**
	 * Expiration timestamp
	 */
	expires_at: DateTime;
	/**
	 * Invalidated timestamp
	 */
	invalidated_at: DateTime|null;
}
