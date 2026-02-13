/**
 * Copyright 2017-2026 IQRF Tech s.r.o.
 * Copyright 2019-2026 MICRORISC s.r.o.
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

import { UserRole } from '@iqrf/iqrf-gateway-webapp-client/types';
import { Language } from '@iqrf/iqrf-ui-common-types';
import { mdiAccount, mdiAccountEye, mdiHelp, mdiShieldAccount } from '@mdi/js';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test } from 'vitest';

import UserRoleBadge from '@/components/access-control/users/UserRoleBadge.vue';
import i18n from '@/plugins/i18n';
import { pluginFactory } from '@/tests/factories/pluginFactory';


describe('UserRoleBadge', (): void => {

	interface TestCase {
		/// User role
		role: UserRole;
		/// Expected badge color
		color: string;
		/// Expected badge icon
		icon: string;
		/// Expected badge text
		text: string;
	}

	/**
	 * @const {TestCase[]} cases Test cases
	 */
	const cases: TestCase[] = [
		{
			role: UserRole.Admin,
			color: 'deep-purple',
			icon: mdiShieldAccount,
			text: 'Administrator',
		},
		{
			role: UserRole.Normal,
			color: 'indigo',
			icon: mdiAccount,
			text: 'Normal user',
		},
		{
			role: UserRole.Basic,
			color: 'teal',
			icon: mdiAccountEye,
			text: 'Basic user',
		},
	];

	beforeEach((): void => {
		// @ts-ignore Accessing ComputedRef
		i18n.global.locale.value = Language.English;
	});

	test.each(cases)('$text', ({ role, color, icon, text }: TestCase): void => {
		expect.assertions(3);
		const wrapper = mount(UserRoleBadge, {
			props: {
				role: role,
			},
			global: {
				plugins: pluginFactory(i18n),
			},
		});

		// @ts-ignore Accessing private property
		expect(wrapper.vm.color).toStrictEqual(color);
		// @ts-ignore Accessing private property
		expect(wrapper.vm.icon).toStrictEqual(icon);
		// @ts-ignore Accessing private property
		expect(wrapper.vm.text).toStrictEqual(text);
	});

	test('Invalid user role', (): void => {
		expect.assertions(3);
		const wrapper = mount(UserRoleBadge, {
			props: {
				// @ts-ignore Invalid account state
				role: 'Invalid',
			},
			global: {
				plugins: pluginFactory(i18n),
			},
		});

		// @ts-ignore Accessing private property
		expect(wrapper.vm.color).toStrictEqual('grey');
		// @ts-ignore Accessing private property
		expect(wrapper.vm.icon).toStrictEqual(mdiHelp);
		// @ts-ignore Accessing private property
		expect(wrapper.vm.text).toStrictEqual('');
	});

});
