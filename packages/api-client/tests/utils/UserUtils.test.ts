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

import { describe, expect, it } from 'vitest';

import {
	AccountState,
	type UserBase,
	type UserEdit,
	type UserInfo,
	UserLanguage,
	UserRole,
} from '../../src/types';
import { UserUtils } from '../../src/utils';

describe('UserUtils', (): void => {

	/**
	 * Common information about the user
	 */
	const user: UserBase = {
		username: 'admin',
		email: null,
		role: UserRole.Admin,
		language: UserLanguage.English,
	};

	/**
	 * User information with ID and state
	 */
	const userInfo: UserInfo = {
		...user,
		id: 1,
		state: AccountState.Verified,
	};

	it('serialize User without e-mail address (null)', (): void => {
		expect.assertions(1);
		const userToSerialize: UserEdit = {
			...user,
			password: 'password',
		};
		const actual: UserEdit = UserUtils.serialize(userToSerialize);
		expect(actual).toStrictEqual({
			...user,
			password: 'password',
		});
	});

	it('serialize User without e-mail address (empty string)', (): void => {
		expect.assertions(1);
		const userToSerialize: UserEdit = {
			...user,
			email: '',
		};
		const actual: UserEdit = UserUtils.serialize(userToSerialize);
		expect(actual).toStrictEqual({
			...user,
			email: null,
		});
	});

	it('serialize User with e-mail address', (): void => {
		expect.assertions(1);
		const userToSerialize: UserEdit = {
			...user,
			email: 'admin@example.com',
		};
		const actual: UserEdit = UserUtils.serialize(userToSerialize);
		expect(actual).toStrictEqual({
			...user,
			email: 'admin@example.com',
		});
	});

	it('serialize User with Unicode IDN e-mail address', (): void => {
		expect.assertions(1);
		const userToSerialize: UserEdit = {
			...user,
			email: 'admin@háčkyčárky.cz',
		};
		const actual: UserEdit = UserUtils.serialize(userToSerialize);
		expect(actual).toStrictEqual({
			...user,
			email: 'admin@xn--hkyrky-ptac70bc.cz',
		});
	});

	it('serialize User with ASCII IDN e-mail address', (): void => {
		expect.assertions(1);
		const userToSerialize: UserEdit = {
			...user,
			email: 'admin@xn--hkyrky-ptac70bc.cz',
		};
		const actual: UserEdit = UserUtils.serialize(userToSerialize);
		expect(actual).toStrictEqual({
			...user,
			email: 'admin@xn--hkyrky-ptac70bc.cz',
		});
	});

	it('deserialize User without e-mail address', (): void => {
		expect.assertions(1);
		const userToDeserialize: UserInfo = {
			...userInfo,
			email: null,
		};
		const actual: UserInfo = UserUtils.deserialize(userToDeserialize);
		expect(actual).toStrictEqual({
			...userInfo,
			email: null,
		});
	});

	it('deserialize User with e-mail address', (): void => {
		expect.assertions(1);
		const userToDeserialize: UserInfo = {
			...userInfo,
			email: 'admin@example.com',
		};
		const actual: UserInfo = UserUtils.deserialize(userToDeserialize);
		expect(actual).toStrictEqual({
			...userInfo,
			email: 'admin@example.com',
		});
	});

	it('deserialize User with IDN e-mail address', (): void => {
		expect.assertions(1);
		const userToDeserialize: UserInfo = {
			...userInfo,
			email: 'admin@xn--hkyrky-ptac70bc.cz',
		};
		const actual: UserInfo = UserUtils.deserialize(userToDeserialize);
		expect(actual).toStrictEqual({
			...userInfo,
			email: 'admin@háčkyčárky.cz',
		});
	});

});
