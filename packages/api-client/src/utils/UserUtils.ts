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

import * as punycode from 'punycode/';

import { type UserCreate, type UserEdit, type UserInfo, type UserSignedIn } from '../types';

/**
 * User utilities
 * @internal
 */
export class UserUtils {

	/**
	 * Serializes the user
	 * @template {UserCreate|UserEdit} T Type of the user
	 * @param {T} user User to serialize
	 * @return {T} Serialized user
	 */
	public static serialize<T extends UserCreate|UserEdit>(user: T): T {
		user.email = (user.email === null || user.email.length === 0) ? null : punycode.toASCII(user.email);
		return user;
	}

	/**
	 * Deserializes the user
	 * @template {UserInfo|UserSignedIn} T Type of the user
	 * @param {T} user User to deserialize
	 * @return {T} Deserialized user
	 */
	public static deserialize<T extends UserInfo|UserSignedIn>(user: T): T {
		user.email = (user.email === null || user.email.length === 0) ? null : punycode.toUnicode(user.email);
		return user;
	}

}
