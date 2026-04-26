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

import { type RoleInfo } from '@iqrf/iqrf-gateway-webapp-client/types/Security';
import { Language } from '@iqrf/iqrf-ui-common-types';
import { mdiAccount, mdiAccountEye, mdiShieldAccount } from '@mdi/js';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test } from 'vitest';

import RoleBadge from '@/components/access-control/roles/RoleBadge.vue';
import i18n from '@/plugins/i18n';
import { pluginFactory } from '@/tests/factories/pluginFactory';

describe('RoleBadge', (): void => {

	interface TestCase {
		/// Role
		role: RoleInfo;
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
			role: {
				id: 1,
				name: 'Admin',
				description: 'Administrator role',
				scopes: [],
				system: true,
				systemKey: 'admin',
			},
			color: 'deep-purple',
			icon: mdiShieldAccount,
			text: 'Administrator',
		},
		{
			role: {
				id: 2,
				name: 'Normal',
				description: 'Normal role',
				scopes: [],
				system: true,
				systemKey: 'normal',
			},
			color: 'indigo',
			icon: mdiAccount,
			text: 'Normal user',
		},
		{
			role: {
				id: 3,
				name: 'Viewer',
				description: 'Viewer role',
				scopes: [],
				system: true,
				systemKey: 'viewer',
			},
			color: 'teal',
			icon: mdiAccountEye,
			text: 'Viewer',
		},
	];

	beforeEach((): void => {
		// @ts-ignore Accessing ComputedRef
		i18n.global.locale.value = Language.English;
	});

	test.each(cases)('$text', ({ role, color, icon, text }: TestCase): void => {
		expect.assertions(3);
		const wrapper = mount(RoleBadge, {
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

	test('Custom role without system key', (): void => {
		expect.assertions(3);
		const wrapper = mount(RoleBadge, {
			props: {
				role: {
					id: 4,
					name: 'Custom role',
					description: 'Custom role description',
					scopes: [],
					system: false,
					systemKey: null,
				},
			},
			global: {
				plugins: pluginFactory(i18n),
			},
		});

		// @ts-ignore Accessing private property
		expect(wrapper.vm.color).toStrictEqual('grey');
		// @ts-ignore Accessing private property
		expect(wrapper.vm.icon).toStrictEqual(mdiAccount);
		// @ts-ignore Accessing private property
		expect(wrapper.vm.text).toStrictEqual('Custom role');
	});

});
