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

import { type AccessScope } from './AccessScope';

/**
 * Role configuration
 */
export interface RoleConfig {

	/**
	 * Role name
	 */
	name: string;

	/**
	 * Role description
	 */
	description: string;

	/**
	 * Role access scopes
	 */
	scopes: AccessScope[];

}

/**
 * Role information
 */
export interface RoleInfo extends RoleConfig {

	/**
	 * Role ID
	 */
	id?: number;

	/**
	 * System-managed role flag
	 */
	system?: boolean;

	/**
	 * Stable system role identifier
	 */
	systemKey?: string | null;

}
