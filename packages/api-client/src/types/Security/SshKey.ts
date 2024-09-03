/**
 * Copyright 2023-2024 MICRORISC s.r.o.
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
 * Base SSH key info interface
 */
export interface SshKeyInfoBase {

	/**
	 * SSH key description
	 */
	description: string,

	/**
	 * SSH key hash
	 */
	hash: string,

	/**
	 * SSH key ID
	 */
	id: number,

	/**
	 * SSH public key
	 */
	key: string,

	/**
	 * SSH key type
	 */
	type: string

}

/**
 * SSH key info interface
 */
export interface SshKeyInfo extends SshKeyInfoBase {

	/**
	 * SSH key creation date
	 */
	createdAt: DateTime | null

}

/**
 * SSH key raw info interface
 */
export interface SshKeyInfoRaw extends SshKeyInfoBase {

	/**
	 * SSH key creation date
	 */
	createdAt: string

}

/**
 * SSH key create interface
 */
export interface SshKeyCreate {

	/**
	 * SSH key description
	 */
	description: string

	/**
	 * SSH public key
	 */
	key: string

}

/**
 * SSH key created interface
 */
export interface SshKeyCreated {

	/**
	 * Failed to create SSH keys
	 */
	failedKeys: SshKeyCreate[]

}
