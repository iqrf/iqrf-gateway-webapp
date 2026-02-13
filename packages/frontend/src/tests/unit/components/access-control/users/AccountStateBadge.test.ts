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

import { AccountState } from '@iqrf/iqrf-gateway-webapp-client/types';
import { Language } from '@iqrf/iqrf-ui-common-types';
import { mdiCheck, mdiEmail, mdiHelp, mdiLock } from '@mdi/js';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test } from 'vitest';

import AccountStateBadge from '@/components/access-control/users/AccountStateBadge.vue';
import i18n from '@/plugins/i18n';
import { pluginFactory } from '@/tests/factories/pluginFactory';

describe('AccountStateBadge', (): void => {

	interface TestCase {
		/// Account state
		state: AccountState;
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
			state: AccountState.Blocked,
			color: 'red',
			icon: mdiLock,
			text: 'Blocked',
		},
		{
			state: AccountState.Invited,
			color: 'blue',
			icon: mdiEmail,
			text: 'Invited',
		},
		{
			state: AccountState.Verified,
			color: 'green',
			icon: mdiCheck,
			text: 'Verified',
		},
		{
			state: AccountState.Unverified,
			color: 'orange',
			icon: mdiHelp,
			text: 'Unverified',
		},
	];

	beforeEach((): void => {
		// @ts-ignore Accessing ComputedRef
		i18n.global.locale.value = Language.English;
	});

	test.each(cases)('$text', ({ state, color, icon, text }: TestCase): void => {
		expect.assertions(3);
		const wrapper = mount(AccountStateBadge, {
			props: {
				state: state,
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

	test('Invalid account state', (): void => {
		expect.assertions(3);
		const wrapper = mount(AccountStateBadge, {
			props: {
				// @ts-ignore Invalid account state
				state: 'Invalid',
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
