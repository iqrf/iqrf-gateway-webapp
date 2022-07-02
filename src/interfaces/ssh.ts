/**
 * Copyright 2017-2022 IQRF Tech s.r.o.
 * Copyright 2019-2022 MICRORISC s.r.o.
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
 * SSH key list intreface
 */
export interface ISshKey {
	/**
	 * SSH key ID
	 */
	id: number

	/**
	 * SSH key description
	 */
	description: string

	/**
	 * SSH key type
	 */
	type: string

	/**
	 * SSH key hash
	 */
	hash: string

	/**
	 * SSH key
	 */
	key: string

	/**
	 * SSH key description
	 */
	createdAt: string

	/**
	 * Datatable aux
	 */
	showDetails?: boolean
}

/**
 * Form ssh key interface
 */
export interface ISshInput {
	/**
	 * SSH key description
	 */
	description: string

	/**
	 * SSH public key
	 */
	key: string
}
