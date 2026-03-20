/**
 * Copyright 2023-2025 MICRORISC s.r.o.
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

import { type UserInfo } from '../User';

import { type AccessScope } from './AccessScope';

/**
 * API key configuration
 */
export interface ApiKeyConfig {

	/**
	 * API key description
	 */
	description: string;

	/**
	 * API key expiration date
	 */
	expiration: DateTime | null;

	/**
	 * API key access scopes
	 */
	scopes: AccessScope[];

}

/**
 * API key information
 */
export interface ApiKeyInfo extends ApiKeyConfig {

	/**
	 * API key ID
	 */
	id?: number;

	/**
	 * User who revoked the key
	 */
	revokedBy?: UserInfo;

	/**
	 * Time when key was revoked
	 */
	revokedAt?: DateTime;

	/**
	 * Key state
	 */
	state?: string;

	/**
	 * Legacy key
	 */
	legacy: boolean;

}

/**
 * Created API key
 */
export interface ApiKeyCreated extends ApiKeyInfo {

	/**
	 * API key
	 */
	key: string;

}
